<?php

namespace App\Lib\Components\Notifications\Messages;

abstract class AbstractNotificationMessage
{
    /**
     * Returns all the params inside of an array
     *
     * @return array
     */
    abstract protected function toArrayParams(): array;

    /**
     * Returns the message type
     *
     * @return string
     */
    abstract protected function getMessageType(): string;

    /**
     * Returns the whole message as an array
     *
     * @return array
     */
    final public function toArray(): array{
        $params = $this->toArrayParams();
        $params['notification_type'] = $this->getMessageType();
        return $params;
    }
}
