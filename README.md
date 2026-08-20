# Screenshot Server

A minimal self-hosted screenshot uploader for Linux.

The client script captures a selected screen region, uploads the
result to a small PHP endpoint, and copies the public image URL to the X11
clipboard.

## How it works

1. ImageMagick's `import` command saves a screenshot to
   `/tmp/screenshot_upload.jpg`.
2. `curl` sends the image and a shared token to the PHP endpoint as
   `multipart/form-data`.
3. The server names the file using its MD5 hash, saves it as a `.jpg`, and
   returns its public URL.
4. `xclip` copies the returned URL to the clipboard.

Files are named using the MD5 hash of the uploaded JPEG. A byte-for-byte identical upload overwrites the existing file and retains the same URL.

## Repository contents

| File | Purpose |
| --- | --- |
| `index.php` | Receives and stores uploaded screenshots. |
| `take-a-screenshot.sh` | Captures, uploads, and copies the resulting URL. |

## Requirements

### Server

- A web server with PHP support
- A directory writable by the PHP process
- HTTPS for normal use

The PHP built-in development server can be used for local testing.

### Client

- Linux with an X11 session
- ImageMagick (`import`)
- `curl`
- `xclip`

On Debian or Ubuntu:

```bash
sudo apt update
sudo apt install imagemagick curl xclip
```

## Server setup

1. Clone the repository:

   ```bash
   git clone https://github.com/kuchikuu/screenshot-server.git
   cd screenshot-server
   ```

2. Open `index.php` and replace the placeholder token with a long, random
   secret:

   ```php
   $token = "replace-this-with-a-long-random-token";
   ```

   One way to generate a token is:

   ```bash
   openssl rand -hex 32
   ```

3. Set the public base URL returned after a successful upload. It must end with
   a slash:

   ```php
   echo "https://screenshots.example.com/" . $newfilename;
   ```

4. If screenshots should be stored somewhere other than the directory
   containing `index.php`, change `$uploaddir`:

   ```php
   $uploaddir = "/path/to/screenshot/directory/";
   ```

   The directory must already exist, be writable by PHP, and be served at the
   public base URL configured in the previous step.

5. Place `index.php` in the desired web root and configure your web server to
   execute PHP files.

### Local server test

From the repository directory, start PHP's development server:

```bash
php -S 127.0.0.1:5555
```

Test the endpoint from another terminal:

```bash
curl -F "image=@example.jpg" \
  -F "token=replace-this-with-a-long-random-token" \
  http://127.0.0.1:5555/
```

A successful request returns the configured public URL as plain text.

## Client setup

1. Open `take-a-screenshot.sh` and set `TOKEN` to the same value used by the
   server:

   ```bash
   TOKEN="replace-this-with-a-long-random-token"
   ```

2. Replace `http://server:port` with the URL of the PHP endpoint:

   ```bash
   curl --silent \
     -F "image=@/tmp/screenshot_upload.jpg" \
     -F "token=$TOKEN" \
     https://screenshots.example.com/ | xclip -selection clipboard
   ```

3. Make the script executable:

   ```bash
   chmod +x take-a-screenshot.sh
   ```

## Usage

Run:

```bash
./take-a-screenshot.sh
```

Select a window or drag over a screen region when the crosshair appears. After
the upload finishes, paste the screenshot URL with the usual clipboard
shortcut.

You can bind the script to a desktop environment keyboard shortcut for faster
access.

## Upload API

The endpoint accepts an HTTP `POST` request using `multipart/form-data`.

| Field | Required | Description |
| --- | --- | --- |
| `image` | Yes | The uploaded screenshot file. |
| `token` | Yes (can be omitted) | Shared token configured in `index.php`. |

On success, the response body contains the public image URL. Invalid or missing
tokens produce an empty response. PHP upload errors are returned as plain text.

## Wayland

The supplied client is intended for X11. On a Wayland desktop, `import` and
`xclip` may not work correctly. Replace them with tools supported by your
compositor, such as a screenshot utility together with `wl-copy` from
`wl-clipboard`.

## Security and limitations

- The current server trusts the uploaded content and always gives it a `.jpg`
  extension; it does not verify that the file is actually a JPEG.
- There is no rate limiting, expiration policy, storage quota, or automatic
  cleanup.
- Uploaded screenshots are publicly accessible to anyone who knows their URL.
- Consider restricting upload request size in PHP and your web-server
  configuration.

For public or multi-user deployments, add strict MIME validation, randomized
filenames, rate limiting, logging, and a cleanup policy.

## Troubleshooting

### The script copies an empty value

Run the `curl` command without `--silent` and add `-i` to inspect the response.
Verify that the server URL and token match the values in `index.php`.

### PHP reports an upload-size error

Increase `upload_max_filesize` and `post_max_size` in `php.ini`, then restart
the PHP service.

### The server returns nothing after an upload

Check that the upload directory exists and that the PHP process can write to
it. Also inspect the web-server and PHP error logs.

### Screenshot selection does not appear

Confirm that ImageMagick's `import` command is installed and that the script is
running inside an X11 graphical session.
