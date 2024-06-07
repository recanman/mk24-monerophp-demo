# Workshop: Building a Monero Shop in PHP

June 7, 2024, 17:00

Monero Konference 4, Prague, Czechia

[Talk link](https://cfp.monerokon.org/2024/talk/Q97J9D/)

In this workshop, you will build a small shop using PHP and [monero-wallet-rpc](https://www.getmonero.org/resources/developer-guides/wallet-rpc.html).

Currently, the [Monero-Crypto](https://github.com/monero-integrations/monerophp) and [Monero-RPC](https://github.com/monero-integrations/monerophp-rpc) libraries are undergoing a migration, and we will be using a newer version that is [on another repository](https://github.com/refring/monero-rpc-php), written by [refring](https://github.com/refring).

Run the server with a Podman container (Docker also works):
```sh
podman run -d -p 80:80 --name my-market -v "$PWD":/var/www/html php:8.2-apache
```
