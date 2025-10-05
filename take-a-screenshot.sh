#!/bin/bash
TOKEN="your token here"
import /tmp/screenshot_upload.jpg
#for details, add -i to curl and remove --silent
curl --silent -F "image=@/tmp/screenshot_upload.jpg" -F "token=$TOKEN" http://server:port | xclip -selection clipboard
