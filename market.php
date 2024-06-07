<?php
require_once __DIR__ . '/vendor/autoload.php';

// Some predefined functions

// These two act as a 'database'.
function get_server_state()
{
    $data = file_get_contents('./data.json');
    return json_decode($data, true);
}

function set_server_state($state)
{
    file_put_contents('./data.json', json_encode($state, JSON_PRETTY_PRINT));
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

	    //TODO: append items to container
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

// TODO: implement these functions
function processOrder(int $itemId)
{
}

function processPayment(string $orderId)
{
}
?>
