<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 2.6.2015
 */

namespace POS\Ext;

/**
 * Pravidla schvalování fotek.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImageRules {

	public static $rules = array(
		array(
			'info' => 'Na profilové fotce nesmí být pouze přirození.',
			'more' => 'Vzhledem k tomu, že profilová fotka slouží k tomu, aby si vás ostaní uživatelé mohli prohlédnout, není vhodné aby na ní bylo z větší části pouze pánské nebo dámské přirození. Tyto fotky si nahrávejte do svých galerií, profilou fotku však zvolte jinou.'
		),
		array(
			'info' => 'Na profilové fotce musíte být vy.',
			'more' => 'Vzhledem k tomu, že profilová fotka slouží k tomu, aby si vás ostaní uživatelé mohli prohlédnout, na profilové fotce nesmí být obrázek stáhnutý z internetu (kočka, známá osobnost a pod.)'
		)
	);

}
