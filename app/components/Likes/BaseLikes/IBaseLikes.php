<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

/**
 * Rozhraní pro komponenty obsluhující lajkování
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IBaseLikes {

	public function handleSexy($userID, $imageID);
}
