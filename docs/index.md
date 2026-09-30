## Documentation

### Tracking code modification to provide necessary data

Place these lines before `_paq.push(['trackPageView']);`:

- Pregenerated hash (PHP example)
  ```javascript
  _paq.push(['UltimateProfileAvatar.setLibravatarHash', '<?=hash('sha256', 'some@email.addr');?>']);
  ```
  Value must be a 64-character lowercase hexadecimal SHA-256 hash of a lowercase e-mail.

- E-mail as User ID
  ```javascript
  _paq.push([['setUserId', 'some@email.addr']);
  ```

Pregenerated hash has a precedence over User ID when both are present.

### Manual request

You can also use the `libravatar_hash` parameter to provide pregenerated hash via HTTP request:
```
https://yourdomain.com/matomo.php?idsite=1&rec=1&url=https://example.com&libravatar_hash=XXXXXXXXX`
```
