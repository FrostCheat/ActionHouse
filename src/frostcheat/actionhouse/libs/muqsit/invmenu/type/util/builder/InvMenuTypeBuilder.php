<?php

declare(strict_types=1);

namespace frostcheat\actionhouse\libs\muqsit\invmenu\type\util\builder;

use frostcheat\actionhouse\libs\muqsit\invmenu\type\InvMenuType;

interface InvMenuTypeBuilder{

	public function build() : InvMenuType;
}