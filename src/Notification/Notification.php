<?php

namespace Mdtt\Notification;

interface Notification
{
    /**
     * Sends message.
     * @param string $title
     * @param string $message
     * @param string $receiver
     *
     * @return void
     */
    public function sendMessage(string $title, string $message, string $receiver): void;
}
