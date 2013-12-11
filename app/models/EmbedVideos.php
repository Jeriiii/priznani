<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class EmbedVideos extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('embed_videos', $connection);
    }
}
