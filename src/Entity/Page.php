<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $parent_page;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $text = preg_replace("~\*\*([a-zA-Zа-яА-Я\s\w]+)\*\*~", "<b>$1</b>", $text);
        $text = preg_replace("~//([a-zA-Zа-яА-Я\s\w]+)//~", "<i>$1</i>", $text);
        $text = preg_replace("~__([a-zA-Zа-яА-Я\s\w]+)__~", "<u>$1</u>", $text);
        $this->text = $text;

        return $this;
    }

    public function markLinks(ManagerRegistry $doctrine)
    {
        $route_links = preg_match_all("~\[\[([a-zA-Zа-яА-Я0-9\s_]+)\|([a-zA-Zа-яА-Я0-9\s_]+)\]\]~miu", $this->text, $match,PREG_SET_ORDER);
        if ($route_links) {
            foreach ($match as $match_item) {
                $regexp = str_replace("]", "\]", $match_item[0]);
                $regexp = str_replace("[", "\[", $regexp);
                $regexp = str_replace("|", "\|", $regexp);
                $has_route = null !== $doctrine->getRepository(Page::class)->findOneBy(['address' => $match_item[1]]);
                $this->text = preg_replace("~$regexp~", "<a class='" . ($has_route ? 'btn-link' : 'damaged-link') . "' href='/{$match_item[1]}'>{$match_item[2]}</a>", $this->text);
            }
        }
        $route_links2 = preg_match_all("~\[\[(http|https)://([a-zA-Zа-яА-Я0-9\s._-]+)\|([a-zA-Zа-яА-Я0-9\s_.-]+)\]\]~miu", $this->text, $match,PREG_SET_ORDER);
        dump($match);
        if ($route_links2) {
            foreach ($match as $match_item) {
                $regexp = str_replace("]", "\]", $match_item[0]);
                $regexp = str_replace("[", "\[", $regexp);
                $regexp = str_replace("|", "\|", $regexp);
                $this->text = preg_replace("~$regexp~", "<a class='btn-link' href='{$match_item[1]}://{$match_item[2]}'>{$match_item[3]}</a>", $this->text);
            }
        }
        $route_links3 = preg_match_all("~\[\[(http|https):\/\/([a-z\.A-Z_-]+)\]~miu", $this->text, $match,PREG_SET_ORDER);
        if ($route_links3) {
            foreach ($match as $match_item) {
                $regexp = str_replace("]", "\]", $match_item[0]);
                $regexp = str_replace("[", "\[", $regexp);
                $regexp = str_replace("|", "\|", $regexp);
                $this->text = preg_replace("~$regexp~", "<a class='btn-link' href='{$match_item[1]}://{$match_item[2]}'>{$match_item[1]}://{$match_item[2]}</a>", $this->text);
            }
        }

        return $this->text;
    }

    public function setWikiText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getParentPage(): ?string
    {
        return $this->parent_page;
    }

    public function setParentPage(string $parent_page): self
    {
        $this->parent_page = $parent_page;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
