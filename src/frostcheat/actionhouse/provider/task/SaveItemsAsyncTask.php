<?php

namespace frostcheat\actionhouse\provider\task;

use pocketmine\scheduler\AsyncTask;

class SaveItemsAsyncTask extends AsyncTask
{
    private string $file;
    private string $data;

    public function __construct(string $file, array $items) {
        $this->file = $file;
        $this->data = yaml_emit($items);
    }

    public function onRun(): void {
        file_put_contents($this->file, $this->data);
    }
}