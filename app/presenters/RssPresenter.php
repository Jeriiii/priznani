<?php

/**
 * Description of RssPresenter
 *
 * @author Honza
 */

class RssPresenter extends BasePresenter {

    /**
     * (non-phpDoc)
     *
     * @see Nette\Application\Presenter#startup()
     */
    protected function startup() {
        parent::startup();
    }

    public function actionDefault() {
        
    }

    public function renderDefault($id_serie) {
        /* @var RssControl */
        $rss = $this["rss"];
        if($id_serie){
        $serie = $this->context->createSeries()
                ->find($id_serie)
                ->fetch();
        }else{
            $serie->name = 'na jadropudla.cz';
        }
        $rsslink = $this->link('//Rss:', array('id_serie' => $serie->id));
        
        // properties
        $rss->title = "Jádro pudla - ". $serie->name;
        $rss->description = "Nové díly komiksu ". $serie->name;
        $rss->link = $this->link("//Homepage:");
        $rss->setChannelProperty("language", 'cs');//
        $rss->setChannelProperty("ttl", 120);//minuty do obnoveni
        $rss->setChannelProperty("link", $rsslink);
        $rss->setChannelProperty("lastBuildDate", \Rss\RssControl::prepareDate(time()));
        // je možno použít odpovídající metody setTitle, setDescription, setLink
        // pro úpravu vlastností kanálu lze využít událost $onPrepareProperties

        // items
        if($id_serie){
        $dbitems = $this->context->createComixs()
                ->getPublishedComix($serie->id)
                ->order('issue DESC')
                ->limit(20, 0);
        }else{
            $dbitems = $this->context->createComixs()
                ->order('issue DESC')
                ->limit(20, 0);
        }
        $items = array();

        // úprava, lze také využít události $onPrepareItem
        foreach ($dbitems as $dbitem) {
            $items[$dbitem->id] = array();
            $items[$dbitem->id]["title"] = $dbitem->name;
            $items[$dbitem->id]["link"] = $this->link('//Viewcomix:', array('id' => $serie->id, 'vp-page' => $dbitem->issue));
            $items[$dbitem->id]["guid"] = $this->link('//Viewcomix:', array('id' => $serie->id, 'vp-page' => $dbitem->issue));
            $items[$dbitem->id]["description"] = $dbitem->issue . '. díl komiksu ' . $serie->name;
            $items[$dbitem->id]["pubDate"] = \Rss\RssControl::prepareDate(strtotime($dbitem->release_date));
            //$items[$dbitem->id]["guid"] = $this->link('Viewcomix: id => '.$serie->id.', vp-page => '. $dbitem->id);
        }
        $rss->link = $rsslink;
        $rss->items = $items;
    }

    protected function createComponentRss() {
        return new Rss\RssControl;
    }

}