<?php
require_once __DIR__ . '/vendor/autoload.php';

function print_version($client) {
	$response = $client->getVersion();
	echo "Wallet RPC Version: " . $response->version . PHP_EOL;
}

function restore_from_seed($client, $seed) {
	$walletName = bin2hex(random_bytes(8));
	$wallet = $client->restoreDeterministicWallet($walletName, "123456", $seed, 3000000); // Start from block 3000000
	echo "Wallet Address: " . $wallet->address . PHP_EOL;

	return $wallet;
}

/////
$username = "monero";
$password = "maC8ANQPWHgo10tb/fKDpQ==";
$walletClient = (new \RefRing\MoneroRpcPhp\ClientBuilder('http://127.0.0.1:18082/json_rpc'))
	->withAuthentication($username, $password)
    ->buildWalletClient();

print_version($walletClient);

$seed = "beer sonic feel reorder each light upstairs hunter swiftly madness obvious biplane lower kangaroo gutter soya ailments ashtray arena library vexed unveil jukebox kangaroo vexed";

$wallet = restore_from_seed($walletClient, $seed);
$balance = $walletClient->getBalance(0, null, true);

echo "Wallet Balance: " . $balance->balance . " XMR" . PHP_EOL;

$subaddress = $walletClient->createAddress(0, "Subaddress 1");

echo "Subaddress: " . $subaddress->address . PHP_EOL;