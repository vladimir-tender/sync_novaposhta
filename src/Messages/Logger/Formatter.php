<?php

namespace App\Messages\Logger;


use Monolog\Formatter\LineFormatter;

class Formatter extends LineFormatter
{
    const CUSTOM_FORMAT = "%datetime% %message%\n";
    const DATE_FORMAT = "c";

    public function __construct(?string $format = null, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = false)
    {
        parent::__construct(self::CUSTOM_FORMAT, self::DATE_FORMAT, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }
}
