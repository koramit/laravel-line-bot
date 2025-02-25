<?php

return [
    'bot_basic_id' => env('LINE_BOT_BASIC_ID'),
    'bot_channel_id' => env('LINE_BOT_CHANNEL_ID'),
    'bot_channel_secret' => env('LINE_BOT_CHANNEL_SECRET'),
    'bot_channel_access_token' => env('LINE_BOT_CHANNEL_ACCESS_TOKEN'),
    'bot_add_friend_url' => 'https://line.me/R/ti/p/'.env('LINE_BOT_BASIC_ID'),
    'bot_push_endpoint' => env('LINE_BOT_PUSH_ENDPOINT', 'https://api.line.me/v2/bot/message/push'),
    'bot_reply_endpoint' => env('LINE_BOT_REPLY_ENDPOINT', 'https://api.line.me/v2/bot/message/reply'),
    'bot_verify_code_length' => env('LINE_BOT_VERIFY_CODE_LENGTH', 4),
    'bot_get_user_profile_endpoint' => env('LINE_BOT_GET_USER_PROFILE_ENDPOINT', 'https://api.line.me/v2/bot/profile/'),
    'bot_disconnect_command' => env('LINE_BOT_DISCONNECT_COMMAND', '/disconnect'),
];
