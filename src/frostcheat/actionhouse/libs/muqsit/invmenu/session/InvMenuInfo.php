<?php

declare(strict_types=1);

namespace frostcheat\actionhouse\libs\muqsit\invmenu\session;

use frostcheat\actionhouse\libs\muqsit\invmenu\InvMenu;
use frostcheat\actionhouse\libs\muqsit\invmenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}