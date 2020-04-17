<?php

namespace Models;

use PDO;

/**
 * Class Information
 *
 * Information entity
 *
 * @package Models
 */
class Information extends Model implements Entity
{

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var User
	 */
	private $author;

	/**
	 * @var string
	 */
	private  $creationDate;

	/**
	 * @var string
	 */
	private $expirationDate;

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var string (Text | Image | excel | PDF | Event)
	 */
	private $type;

    /**
     * @var int
     */
	private $adminId;

    /**
     * Add the information in the database with today date and current user.
     *
     * @return int
     */
    public function insert()
    {
	    $request = $this->getDatabase()->prepare('INSERT INTO ecran_information (title, author, creation_date, expiration_date, content, type, administration_id) VALUES (:title, :author, :creation_date, :expiration_date, :content, :type, :administrationId)');

	    $request->bindValue(':title', $this->getTitle(), PDO::PARAM_STR);
	    $request->bindValue(':author', $this->getAuthor(), PDO::PARAM_INT);
	    $request->bindValue(':creation_date', $this->getCreationDate(), PDO::PARAM_STR);
	    $request->bindValue(':expiration_date', $this->getExpirationDate(), PDO::PARAM_STR);
	    $request->bindValue(':content', $this->getContent(), PDO::PARAM_STR);
	    $request->bindValue(':type', $this->getType(), PDO::PARAM_STR);
        $request->bindValue(':administrationId', $this->getAdminId(), PDO::PARAM_STR);

	    $request->execute();

	    return $this->getDatabase()->lastInsertId();
    }

	/**
	 * Modify the information in database
	 */
	public function update()
	{
		$request = $this->getDatabase()->prepare('UPDATE ecran_information SET title = :title, content = :content, expiration_date = :expiration_date WHERE id = :id');

		$request->bindValue(':id', $this->getId(), PDO::PARAM_INT);
		$request->bindValue(':title', $this->getTitle(), PDO::PARAM_STR);
		$request->bindValue(':content', $this->getContent(), PDO::PARAM_STR);
		$request->bindValue(':expiration_date', $this->getExpirationDate(), PDO::PARAM_STR);

		$request->execute();

		return $request->rowCount();
	} //modifyInformation()

    /**
     * Delete an information in the database
     */
    public function delete()
    {
	    $request = $this->getDatabase()->prepare('DELETE FROM ecran_information WHERE id = :id');

	    $request->bindValue(':id', $this->getId(), PDO::PARAM_INT);

	    $request->execute();

	    return $request->rowCount();
    } //deleteInformation()

    /**
     * Return an information corresponding to the ID
     *
     * @param $id   int id
     *
     * @return Information | bool
     */
    public function get($id)
    {
        $request = $this->getDatabase()->prepare("SELECT id, title, content, creation_date, expiration_date, author, type FROM ecran_information WHERE id = :id LIMIT 1");

	    $request->bindParam(':id', $id, PDO::PARAM_INT);

	    $request->execute();

	    if($request->rowCount() > 0) {
            return $this->setEntity($request->fetch(PDO::FETCH_ASSOC));
        }
	    return false;
    }

    /**
     * @param int $begin
     * @param int $numberElement
     *
     * @return Information[]|void
     */
    public function getList($begin = 0, $numberElement = 25)
    {
        $request = $this->getDatabase()->prepare("SELECT id, title, content, creation_date, expiration_date, author, type FROM ecran_information LIMIT :begin, :numberElement");

        $request->bindValue(':begin', (int) $begin, PDO::PARAM_INT);
        $request->bindValue(':numberElement', (int) $numberElement, PDO::PARAM_INT);

        $request->execute();

        if ($request->rowCount() > 0) {
            return $this->setEntityList($request->fetchAll());
        }
        return [];
    }

    /**
     * Return the list of information created by an user
     *
     * @param $authorId     int id
     *
     * @return Information[]
     */
    public function getAuthorListInformation($author, $begin = 0, $numberElement = 25)
    {
        $request = $this->getDatabase()->prepare( 'SELECT * FROM ecran_information WHERE author = :author ORDER BY expiration_date LIMIT :begin, :numberElement');

        $request->bindParam(':author', $author, PDO::PARAM_INT);
        $request->bindValue(':begin', (int) $begin, PDO::PARAM_INT);
        $request->bindValue(':numberElement', (int) $numberElement, PDO::PARAM_INT);

        $request->execute();

        return $this->setEntityList($request->fetchAll(PDO::FETCH_ASSOC));
    } //getAuthorListInformation()

    public function countAll()
    {
        $request = $this->getDatabase()->prepare("SELECT COUNT(*) FROM ecran_information");

        $request->execute();

        return $request->fetch()[0];
    }

    /**
     * Return the list of event present in database
     * @return array|null|object
     */
    public function getListInformationEvent()
    {
        $request = $this->getDatabase()->prepare('SELECT * FROM ecran_information WHERE type = "event" ORDER BY expiration_date ASC');

        $request->execute();

        return $this->setEntityList($request->fetchAll(PDO::FETCH_ASSOC));
    }


    /**
     * @return Information[]
     */
    public function getFromAdminWebsite()
    {
        $request = $this->getDatabaseViewer()->prepare('SELECT id, title, content, type, author, expiration_date, creation_date FROM ecran_information LIMIT 200');

        $request->execute();

        return $this->setEntityList($request->fetchAll(), true);
    }

    /**
     *
     * @return Information[]
     */
    public function getAdminWebsiteInformation()
    {
        $request = $this->getDatabase()->prepare('SELECT id, title, content, type, author, expiration_date, creation_date FROM ecran_information WHERE administration_id IS NOT NULL LIMIT 500');

        $request->execute();

        return $this->setEntityList($request->fetchAll());
    }

    /**
     * @param $id
     * @return $this|bool|Information
     */
    public function getInformationFromAdminSite($id)
    {
        $request = $this->getDatabaseViewer()->prepare('SELECT id, title, content, type, author, expiration_date, creation_date FROM ecran_information WHERE id = :id LIMIT 1');

        $request->bindValue(':id', $id, PDO::PARAM_INT);

        $request->execute();

        if($request->rowCount() > 0) {
            return $this->setEntity($request->fetch(), true);
        }
        return false;
    }

	/**
	 * Build a list of informations
	 *
	 * @param $dataList
	 *
	 * @return array | Information
	 */
	public function setEntityList($dataList, $adminSite = false)
	{
		$listEntity = array();
		foreach ($dataList as $data) {
			$listEntity[] = $this->setEntity($data, $adminSite);
		}
		return $listEntity;
	}


	/**
	 * Create an information
	 *
	 * @param $data
	 *
	 * @return $this
	 */
	public function setEntity($data, $adminSite = false)
	{
		$entity = new Information();
		$user = new User();

		$entity->setId($data['id']);
		$entity->setTitle($data['title']);
        $entity->setContent($data['content']);
        $entity->setCreationDate(date('Y-m-d', strtotime($data['creation_date'])));
        $entity->setExpirationDate(date('Y-m-d', strtotime($data['expiration_date'])));
		$entity->setAuthor($user->get($data['author']));
		$entity->setType($data['type']);

        if($adminSite) {
            $entity->setAdminId($data['id']);
        } else {
            $entity->setAdminId($data['administration_id']);
        }

		return $entity;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return User
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @param $author
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
	}

	/**
	 * @return string
	 */
	public function getCreationDate()
	{
		return $this->creationDate;
	}

	/**
	 * @param mixed $creationDate
	 */
	public function setCreationDate($creationDate)
	{
		$this->creationDate = $creationDate;
	}

	/**
	 * @return string
	 */
	public function getExpirationDate()
	{
		return $this->expirationDate;
	}

	/**
	 * @param $expirationDate
	 */
	public function setExpirationDate($expirationDate)
	{
		$this->expirationDate = $expirationDate;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

    /**
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * @param int $adminId
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
    }
}