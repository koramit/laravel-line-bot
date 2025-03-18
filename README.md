# Laravel LINE bot

## Installation
`composer require koramit/laravel-line-bot`

`php artisan vendor:publish --provider="Koramit\LaravelLINEBot\LINEBotServiceProvider"`

`php artisan migrate`

```aiignore
# .env required configs
LINE_BOT_BASIC_ID=
LINE_BOT_CHANNEL_ID=
LINE_BOT_CHANNEL_SECRET=
LINE_BOT_CHANNEL_ACCESS_TOKEN=

# .env optional configs
LINE_BOT_VERIFY_CODE_LENGTH=4
LINE_BOT_PUSH_ENDPOINT=https://api.line.me/v2/bot/message/push
LINE_BOT_REPLY_ENDPOINT=https://api.line.me/v2/bot/message/reply
LINE_BOT_LOADING_ANIMATION_ENDPOINT=https://api.line.me/v2/bot/chat/loading/start
LINE_BOT_GET_USER_PROFILE_ENDPOINT=https://api.line.me/v2/bot/profile/
LINE_VALIDATE_MESSAGE_OBJECT_ENDPOINT=https://api.line.me/v2/bot/message/validate/push
LINE_API_TIMEOUT_SECONDS=10
LINE_API_RETRY_TIMES=3
LINE_API_RETRY_DELAY_MILLISECONDS=200
```
