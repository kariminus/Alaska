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
     * @var \Alaska\Domain\User
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
     * Associated comment id.
     *
     * @var integer
     */
    private $parentId;

    private $children;

    function __construct()
    {
        $this->parentId = 0;
        $this->children = [];
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

    public function setAuthor(User $author) {
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

}