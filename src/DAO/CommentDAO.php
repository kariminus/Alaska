<?php

namespace Alaska\DAO;

use Alaska\Domain\Comment;

class CommentDAO extends DAO
{
    /**
     * @var \Alaska\DAO\ArticleDAO
     */
    private $articleDAO;


    public function setArticleDAO(ArticleDAO $articleDAO) {
        $this->articleDAO = $articleDAO;
    }

    /**
     * Returns a comment matching the supplied id.
     *
     * @param integer $id The comment id
     *
     * @return \Alaska\Domain\Comment|throws an exception if no matching comment is found
     */
    public function find($id) {
        $sql = "select * from t_comment where com_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($id));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No comment matching id " . $id);
    }

    /**
     * Return a list of all comments for an article, sorted by date (most recent last).
     *
     * @param integer $articleId The article id.
     *
     * @return array A list of all comments for the article.
     */
    public function findAllByArticle($articleId) {
        // The associated article is retrieved only once
        $article = $this->articleDAO->find($articleId);

        // art_id is not selected by the SQL query
        // The article won't be retrieved during domain objet construction
        $sql = "select com_id, com_content, author, parent_id, depth, flag from t_comment where art_id=? order by com_id";
        $result = $this->getDb()->fetchAll($sql, array($articleId));

        // Convert query result to an array of domain objects
        $comments = array();
        foreach ($result as $row) {
            $comId = $row['com_id'];
            $comment = $this->buildDomainObject($row);
            // The associated article is defined for the constructed comment
            $comment->setArticle($article);
            $comments[$comId] = $comment;

        }
        return $comments;
    }

    public function findAllWithChildren($articleId, $unsetChildren = true)
    {
        $comments = $commentsById = $this->findAllByArticle($articleId);
        foreach ($comments as $comment)
        {
            if ($comment->getParentId() != 0 )
            {
                $commentsById[$comment->getParentId()]->addChild($comment);
                if ($unsetChildren) {
                    unset($comments[$comment->getId()]);
                }
            }
        }
        return $comments;
    }


    /**
     * Returns a list of all comments, sorted by date (most recent first).
     *
     * @return array A list of all comments.
     */
    public function findAll() {
        $sql = "select * from t_comment order by com_id desc";
        $result = $this->getDb()->fetchAll($sql);

        // Convert query result to an array of domain objects
        $entities = array();
        foreach ($result as $row) {
            $id = $row['com_id'];
            $entities[$id] = $this->buildDomainObject($row);
        }
        return $entities;
    }

    /**
     * Removes all comments for an article
     *
     * @param $articleId The id of the article
     */
    public function deleteAllByArticle($articleId) {
        $this->getDb()->delete('t_comment', array('art_id' => $articleId));
    }


    /**
     * Saves a comment into the database.
     *
     * @param \Alaska\Domain\Comment $comment The comment to save
     */
    public function save(Comment $comment) {
        $commentData = array(
            'art_id' => $comment->getArticle()->getId(),
            'author' => $comment->getAuthor(),
            'com_content' => $comment->getContent(),
            'parent_id' => $comment->getParentId(),
            'depth' => $comment->getDepth(),
            'flag' => $comment->isFlagged()
        );

        if ($comment->getId()) {
            // The comment has already been saved : update it
            $this->getDb()->update('t_comment', $commentData, array('com_id' => $comment->getId()));
        } else {
            // The comment has never been saved : insert it
            $this->getDb()->insert('t_comment', $commentData);
            // Get the id of the newly created comment and set it on the entity.
            $id = $this->getDb()->lastInsertId();
            $comment->setId($id);
        }
    }

    /**
     * Removes a comment from the database.
     *
     * @param @param integer $id The comment id
     */
    public function delete($id) {
        // Delete the comment
        $this->getDb()->delete('t_comment', array('com_id' => $id));
    }

    /**
     * Removes a comment and his children from database
     *
     * @param $id
     */
    public function deleteWithChildren($id) {
        $comment = $this->find($id);
        $comments = $this->findAllWithChildren($comment->getArticle()->getId(), false);
        $ids = $this->getChildrenIds($comments[$comment->getId()]);
        $ids[] = $comment->getId();
        foreach ($ids as $id) {
            $this->delete($id);
        }
    }

    /**
     * Get all chidren ids of a comment
     *
     * @param $comment
     *
     * @return array
     */
    private function getChildrenIds($comment)
    {
        $ids = [];
        foreach ($comment->getChildren() as $child) {
            $ids[] = $child->getId();
            if ($child->getChildren() != null) {
                $ids = array_merge($ids, $this->getChildrenIds($child));
            }
        }
        return $ids;
    }

    /**
     * Flag a comment
     *
     * @param $id
     */
    private function flagComment($id) {
        $comment = $this->find($id);
        $comment->setFlagged(true);
    }

    /**
     * Creates an Comment object based on a DB row.
     *
     * @param array $row The DB row containing Comment data.
     * @return \Alaska\Domain\Comment
     */
    protected function buildDomainObject(array $row) {
        $comment = new Comment();
        $comment->setId($row['com_id']);
        $comment->setContent($row['com_content']);
        $comment->setAuthor($row['author']);
        $comment->setParentId($row['parent_id']);
        $comment->setDepth($row['depth']);

        if (array_key_exists('art_id', $row)) {
            // Find and set the associated article
            $articleId = $row['art_id'];
            $article = $this->articleDAO->find($articleId);
            $comment->setArticle($article);
        }

        return $comment;
    }
}