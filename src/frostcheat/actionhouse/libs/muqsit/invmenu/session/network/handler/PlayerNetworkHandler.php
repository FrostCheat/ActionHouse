<?php

declare(strict_types=1);

namespace frostcheat\actionhouse\libs\muqsit\invmenu\session\network\handler;

use Closure;
use frostcheat\actionhouse\libs\muqsit\invmenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}