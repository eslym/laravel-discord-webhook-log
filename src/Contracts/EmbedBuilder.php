<?php

namespace Eslym\Laravel\Log\DiscordWebhook\Contracts;

interface EmbedBuilder
{
    function buildEmbed(array $record): array;
}