<?php

/**
 * Rozšiřuje třídu Image o další metody
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt;

class Image extends \Nette\Image {

	/**
	 * Vyřízne ze STŘEDU obrázku obdelník
	 *
	 * @param type $height výška výřezu
	 * @param type $width šířka výřezu
	 * @param type $automatic_resize
	 */
	public function cropRtgl($height, $width, $automatic_resize = true) {
		$oldWidth = $this->getWidth();
		$oldHeight = $this->getHeight();

		if ($oldHeight < $height || $oldWidth < $width) {
			if ($automatic_resize) {

				$minSize = $this->getMinSite($height, $width);
				$this->resizeMinSite($minSize);

				$oldHeight = $this->getHeight();
				$oldWidth = $this->getWidth();
			} else {
				throw new Exception("Výška a šířka ořezu musí být nemší než výška a šířka obrázku");
			}
		}

		/* horizontální rozdíl */
		$diffHor = $oldWidth - $width;
		$left = $diffHor / 2;

		/* vertikální rozdíl */
		$diffVer = $oldHeight - $height;
		$top = $diffVer / 2;

		$this->crop($left, $top, $height, $width);
	}

	/**
	 * Vyřízne ze STŘEDU obrázku čtverec
	 *
	 * @param type $newSize délka hrany čtverce
	 * @param type $automatic_resize
	 */
	public function cropSqr($newSize, $automatic_resize = true) {
		$this->cropRtgl($newSize, $newSize, $automatic_resize);
	}

	/**
	 * Zmenší/zvětší obrázek tak, že menší strana bude mít daný rozměr.
	 * Větší strana bude změnšena/zvětšena, aby byl zachován poměr obrázku.
	 *
	 * @param type $newSize nová délka menší hrany
	 */
	public function resizeMinSite($newSize) {
		$width = $this->getWidth();
		$height = $this->getHeight();

		if ($height > $width) {
			$this->resize($newSize, NULL);
		}
		if ($height < $width) {
			$this->resize(NULL, $newSize);
		}
	}

	/**
	 * vrátí kratší stranu
	 *
	 * @param type $height
	 * @param type $width
	 */
	private function getMinSite($height = NULL, $width = NULL) {
		if (empty($height) && empty($width)) {
			$height = $this->getHeight();
			$width = $this->getWidth();
		}

		if ($height <= $width) {
			return $width;
		} else {
			return $height;
		}
	}

}
