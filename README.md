# Ultimate Profile Avatar Plugin

## Description

Display the Libravatar or DiceBear avatar as visitor's profile picture in Matomo.

Supply SHA-256 hash of the visitor's e-mail address with your tracking requests and Matomo will show the matching [Libravatar/Gravatar](https://seccdn.libravatar.org) with a [DiceBear](https://www.dicebear.com) avatar fallback:

- in the **visitor profile**
- in the **visits log**, below browser/device/etc icons (optional)

Plugin also uses hash of a User ID automatically if it's an e-mail.

## Requirements

- Matomo 5.x
- PHP 7.2.5+
- MySQL 8.0+ or MariaDB 10.6+
