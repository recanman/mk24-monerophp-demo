<?php
require_once __DIR__ . '/vendor/autoload.php';

function getItems()
{
    return [
        // price is in atomic units (1 XMR = 1e12 atomic units)
        ['name' => 'Monerochan Plush', 'description' => 'Get the official Monerochan plush!', 'image' => 'monerochan.jpg', 'price' => 10000000000],
        ['name' => 'Monero Sticker', 'description' => 'A cool sticker to show your support for Monero.', 'image' => 'sticker.png', 'price' => 50000000],
        ['name' => 'Apple', 'description' => 'A fresh, tasty apple.', 'image' => 'apple.jpg', 'price' => 100000000],
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
    public string $subaddressIndex;

    public function __construct(string $id, array $item, int $amount, OrderStatus $status, string $subaddressIndex)
    {
        $this->id = $id;
        $this->item = $item;
        $this->amount = $amount;
        $this->status = $status;
        $this->subaddressIndex = $subaddressIndex;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item' => $this->item,
            'amount' => $this->amount,
            'status' => $this->status,
            'subaddressIndex' => $this->subaddressIndex,
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

        .item img:hover {
            transform: scale(3);
            transform-origin: 0 0;
            transition: transform 0.5s;
        }

        input[type="submit"],
        button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        #modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        #modal div {
            background-color: white;
            width: 50%;
            margin: 0 auto;
            margin-top: 10%;
            padding: 20px;
            border-radius: 10px;
        }

        #modal-formatted {
            user-select: all;
            font-family: monospace;
            font-size: 1.2em;
            overflow-wrap: anywhere;
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

    <div id="modal">
        <div>
            <button id="modal-close" onclick="modal.style.display = 'none';" style="float: right;">X</button>

            <h2 id="modal-title">Payment Details</h2>
            <p id="modal-prompt">Please send the specified amount to the following address:</p>
            <p><b id="modal-label">Address:</b> <span id="modal-formatted"></span></p>
            <p><b>Amount:</b> <span id="modal-amount"></span> XMR</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="orderId" value="">
                <input type="submit" value="I have sent the payment">
            </form>
        </div>
    </div>

    <img src="./mk4.jpg" alt="MoneroKon 2024" style="width: 25%; margin-top: 20px;">
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const itemsContainer = document.getElementById('items-container');
            const items = <?php echo json_encode(getItems()); ?>;

            items.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                itemDiv.innerHTML = `
            <h3>${item.name}</h3>
            <img src="${item.image}" alt="${item.name}" style="width: 100px;">
            <p>${item.description}</p>
            <p>Price: ${item.price / 1e12} XMR</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="itemId" value="${index}">
                <input type="submit" value="Buy">
            </form>
        `;
                itemsContainer.appendChild(itemDiv);
            });
        });

        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modal-title');
        const modalPrompt = document.getElementById('modal-prompt');
        const modalLabel = document.getElementById('modal-label');
        const modalFormatted = document.getElementById('modal-formatted');
        const modalAmount = document.getElementById('modal-amount');
        const orderIdInput = document.querySelector('#modal form input[name=orderId]');
        const modalClose = document.getElementById('modal-close');

        const setModal = (orderId, title, prompt, label, formatted, amount, showClose) => {
            modal.style.display = 'block';
            modalTitle.innerText = title;
            modalPrompt.innerText = prompt;
            modalLabel.innerText = label;
            modalFormatted.innerText = formatted;
            modalAmount.innerText = amount;
            orderIdInput.value = orderId;
            modalClose.style.display = showClose ? 'block' : 'none';
        };
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

    $order = new Order($orderId, $item, $item['price'], OrderStatus::Pending, $subaddress->addressIndex);

    $state = get_server_state();
    $state['orders'][] = $order->toArray();
    set_server_state($state);

    echo "Order ID: $orderId";
    $formatted = number_format($item['price'] / 1e12, 12);
    echo "
        <script>
            setModal('$orderId', 'Payment Details', 'Please send the specified amount to the following address:', 'Address:', '$subaddress->address', '$formatted', false);
        </script>
    ";
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
    $rpc->openWallet("wallet", "123456");
    $balance = $rpc->getBalance(0, [(int)$order['subaddressIndex']]);

    $amount = $order['amount'];
    if ($balance->balance < $amount) {
        $formatted_amount = number_format($amount / 1e12, 12);
        $formatted_paid = number_format($balance->balance / 1e12, 12);

        die("
            <script>
                setModal('$orderId', 'Payment Failed', 'Insufficient balance. Please try again.', 'Amount Paid:', '$formatted_paid', '$formatted_amount', true);
            </script>
        ");
    }

    $state['orders'] = array_filter($state['orders'], fn ($o) => $o['id'] !== $orderId);
    set_server_state($state);
}
?>