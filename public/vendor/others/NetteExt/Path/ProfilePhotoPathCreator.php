<?php

/*
 *
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

namespace NetteExt\Path;

use NetteExt\Helper\GetImgPathHelper;

/**
 * Description of ProfilePhotoPathCreator
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ProfilePhotoPathCreator extends PathCreator {

	/**
	 * Vrátí url k profilové fotce
	 * @param ActiveRow $user
	 * @param \NetteExt\Helper\GetImgPathHelper $imgPathHelper
	 * @param bool $minSize
	 * @return string
	 */
	public static function createProfilePhotoUrl($user, $imgPathHelper, $minSize) {
		if (!empty($user->profilFotoID)) {
			if ($minSize) {
				$src = $imgPathHelper->getImgMinPath($user->profilFoto, GetImgPathHelper::TYPE_USER_GALLERY);
			} else {
				$src = $imgPathHelper->getImgScrnPath($user->profilFoto, GetImgPathHelper::TYPE_USER_GALLERY);
			}
		} else {
			if (empty($user->propertyID) || $user->property->type == 1) {
				$type = GetImgPathHelper::TYPE_DEF_PHOTO_MAN;
			} elseif ($user->property->type == 2) {
				$type = GetImgPathHelper::TYPE_DEF_PHOTO_WOMAN;
			} else {
				$type = GetImgPathHelper::TYPE_DEF_PHOTO_COUPLE;
			}
			$src = $imgPathHelper->getImgDefProf($type);
		}

		return $src;
	}

}
