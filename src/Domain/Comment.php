<?php

namespace Alaska\Domain;

class Comment
{
    /**
     * Comment id.
     *
     * @var integer
     */
    private $id;

    /**
     * Comment author.
     *
     * @var string
     */
    private $author;

    /**
     * Comment content.
     *
     * @var integer
     */
    private $content;

    /**
     * Associated article.
     *
     * @var \Alaska\Domain\Article
     */
    private $article;

    /**
     * @var boolean
     */
    private $flagged;


    /**
     * Associated comment id.
     *
     * @var integer
     */
    private $parentId;

    /**
     * @var array
     */
    private $children;

    /**
     * @var int
     */
    private $depth;



    function __construct()
    {
        $this->parentId = 0;
        $this->children = [];
        $this->depth = 0;
        $this->flagged = 0;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getArticle() {
        return $this->article;
    }

    public function setArticle(Article $article) {
        $this->article = $article;
        return $this;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function setParentId($parentId) {
        $this->parentId = $parentId;
        return $this;
    }

    public function addChild(Comment $child) {
        $this->children[] = $child;
    }

    public function getChildren() {
        return $this->children;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return boolean
     */
    public function isFlagged()
    {
        return $this->flagged;
    }

    /**
     * @param boolean $flagged
     */
    public function setFlagged($flagged)
    {
        $this->flagged = $flagged;
    }



}