<?php

namespace NaN\Application\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ServicesApplicationInterface extends ApplicationInterface {
	public PsrContainerInterface $services {
		get;
	}

	public function withServices(PsrContainerInterface $services): ServicesApplicationInterface;
}
