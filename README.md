# yourls-gate-keeper

Protect YOURLS Admin Area with Google's reCAPTCHA v3.

## Installation

1. Copy or `git clone https://github.com/Aternus/yourls-gate-keeper.git` the project to `/user/plugins`.
2. Get reCAPTCHA v3 keys at [Google reCAPTCHA](https://www.google.com/recaptcha/admin).
3. Insert your keys in `/user/config.php`:
   ```php
    define( 'GATE_KEEPER_RECAPTCHA_V3_SITE_KEY', 'x' );
    define( 'GATE_KEEPER_RECAPTCHA_V3_SECRET_KEY', 'y' );
    ```
4. Go to the Admin → Manage Plugins and activate the `Gate Keeper` plugin.
5. Verify everything is working by going to `https://your-yourls-instance.com/admin`, you should see the reCAPTCHA logo
   at the bottom right with no errors.

## License

MIT license

## Contact

Website: https://atern.us
