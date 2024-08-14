<?php
require 'vendor/autoload.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['DISCORD_TOKEN'],
    'intents' => Intents::GUILDS | Intents::GUILD_PRESENCES | Intents::GUILD_MEMBERS | Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT
]);

$discord->on('ready', function ($discord) {
    echo "Bot is ready.", PHP_EOL;

    // Retrieve all guilds the bot is part of
    foreach ($discord->guilds as $guild) {
        $onlineMembers = $guild->members->filter(function ($member) {
            return $member->status === 'online';
        });

        if ($onlineMembers->count() > 0) {
            $usernames = $onlineMembers->map(function ($member) {
                return "{$member->user->username}";
            });
            echo 'Online members in ' . $guild->name . ': ' . implode(', ', $usernames->toArray()), PHP_EOL;
            print_r($onlineMembers);
        } else {
            echo 'There are no online members ' . $guild->name . '.', PHP_EOL;
        }
    }
    
    $discord->on(Event::MESSAGE_CREATE, function ($message, $discord) {
        echo "{$message->author->username}: {$message->content}", PHP_EOL;
        if ($message->content == '!online') {
            $guild = $message->guild;
            if ($guild) {
                $onlineMembers = $guild->members->filter(function ($member) {
                    return $member->status === 'online';
                });

                if ($onlineMembers->count() > 0) {
                    $usernames = $onlineMembers->map(function ($member) {
                        return "{$member->user->username}";
                    });
                    $message->reply('Online members: ' . implode(', ', $usernames->toArray()));
                } else {
                    $message->reply('There are no online members.');
                }
            }
        }
    });
});

$discord->run();