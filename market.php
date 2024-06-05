<?php
require_once __DIR__ . '/vendor/autoload.php';

function getItems()
{
    return [
        // price is in atomic units (1 XMR = 1e12 atomic units)
        ['description' => "Item 1 Description", 'price' => 100],
        ['description' => "Item 2 Description", 'price' => 200],
        ['description' => "Item 3 Description", 'price' => 300],
    ];
}

function get_server_state()
{
    $data = file_get_contents('./data.json');
    return json_decode($data, true);
}

function set_server_state($state)
{
    file_put_contents('./data.json', json_encode($state, JSON_PRETTY_PRINT));
}

function getRpc()
{
    $username = "monero";
    $password = "maC8ANQPWHgo10tb/fKDpQ==";
    $walletClient = (new \RefRing\MoneroRpcPhp\ClientBuilder('http://127.0.0.1:18082/json_rpc'))
        ->withAuthentication($username, $password)
        ->buildWalletClient();

    return $walletClient;
}

enum OrderStatus: int
{
    case Pending = 0;
    case Paid = 1;
    case Completed = 2;
}

final class Order
{
    public string $id;
    public array $item;
    public int $amount;
    public OrderStatus $status;
    public string $subaddress;

    public function __construct(string $id, array $item, int $amount, OrderStatus $status, string $subaddress)
    {
        $this->id = $id;
        $this->item = $item;
        $this->amount = $amount;
        $this->status = $status;
        $this->subaddress = $subaddress;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item' => $this->item,
            'amount' => $this->amount,
            'status' => $this->status,
            'subaddress' => $this->subaddress,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>MoneroKon 2024 Marketplace Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        #top-bar {
            background-color: #4c4c4c;
            padding: 10px;
            text-align: center;
            color: white;
            font-weight: bold;
        }

        #items-container {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            flex-direction: column;
        }

        .item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        input[type="submit"],
        button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div id="top-bar">Marketplace Demo</div>
    <p>Welcome to my Monero marketplace! Please select an item to purchase:</p>

    <div id="items-container"></div>

    <form id="order-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="orderId" placeholder="Order ID" required>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const itemsContainer = document.getElementById('items-container');
            const items = <?php echo json_encode(getItems()); ?>;

            items.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                itemDiv.innerHTML = `
            <h3>${item.description}</h3>
            <p>Price: ${item.price} atomic units</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="itemId" value="${index}">
                <input type="submit" value="Buy">
            </form>
        `;
                itemsContainer.appendChild(itemDiv);
            });
        });

        document.getElementById('order-form').addEventListener('submit', async (event) => {
            document.getElementById('payment-modal').style.display = 'block';
        });
    </script>

</body>

</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["itemId"]) && isset($_POST["orderId"])) {
        die("Invalid request");
    }

    if (isset($_POST["itemId"])) {
        processOrder($_POST["itemId"]);
    } else if (isset($_POST["orderId"])) {
        processPayment($_POST["orderId"]);
    }

    die();
}

function processOrder(int $itemId)
{
    $items = getItems();
    if (!isset($items[$itemId])) {
        die("Invalid item ID $itemId");
    }
    $item = $items[$itemId];

    $orderId = bin2hex(random_bytes(16));

    $rpc = getRpc();
    $rpc->openWallet("wallet", "123456");
    $subaddress = $rpc->createAddress(0, "Subaddress for Order $orderId");

    $order = new Order($orderId, $item, $item['price'], OrderStatus::Pending, $subaddress->address);

    $state = get_server_state();
    $state['orders'][] = $order->toArray();
    set_server_state($state);

    echo "Order ID: $orderId";
    $formatted = number_format($item['price'] / 1e12, 12);
    echo "<hr><p>Please send <b>" . $formatted . " XMR</b> to the following subaddress: <b><pre>" . $subaddress->address . "</pre></b></p>";
    echo "<form action=\"market.php\" method=\"post\"><input type=\"text\" name=\"orderId\" value=\"$orderId\"><button type=\"submit\">Check Payment</button></form>";
}

function processPayment(string $orderId)
{
    $state = get_server_state();
    $order = null;

    foreach ($state['orders'] as $o) {
        if ($o['id'] === $orderId) {
            $order = $o;
            break;
        }
    }

    if ($order === null) {
        die("Invalid order ID $orderId");
    }

    $rpc = getRpc();
    $wallet = $rpc->openWallet("wallet", "123456");
    $balance = $rpc->getBalance(0, null, true);

    if ($balance->balance < $order['amount']) {
        die("Insufficient balance");
    }

    $state['orders'] = array_filter($state['orders'], fn ($o) => $o['id'] !== $orderId);
    set_server_state($state);
}
?>