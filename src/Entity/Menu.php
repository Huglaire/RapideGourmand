<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $minimumGuestNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conditions = null;

    #[ORM\Column]
    private ?bool $isAvailable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Theme>
     */
    #[ORM\ManyToMany(targetEntity: Theme::class, inversedBy: 'menus')]
    private Collection $theme;

    /**
     * @var Collection<int, Diet>
     */
    #[ORM\ManyToMany(targetEntity: Diet::class, inversedBy: 'menus')]
    private Collection $diets;

    /**
     * @var Collection<int, Dish>
     */
    #[ORM\ManyToMany(targetEntity: Dish::class, inversedBy: 'menus')]
    private Collection $dishes;

    /**
     * @var Collection<int, Picture>
     */
    #[ORM\ManyToMany(targetEntity: Picture::class, inversedBy: 'menus')]
    private Collection $pictures;

    /**
     * @var Collection<int, OrderMenu>
     */
    #[ORM\OneToMany(targetEntity: OrderMenu::class, mappedBy: 'menu')]
    private Collection $orderMenus;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isAvailable = true;
        $this->theme = new ArrayCollection();
        $this->diets = new ArrayCollection();
        $this->dishes = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->orderMenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMinimumGuestNumber(): ?int
    {
        return $this->minimumGuestNumber;
    }

    public function setMinimumGuestNumber(int $minimumGuestNumber): static
    {
        $this->minimumGuestNumber = $minimumGuestNumber;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(?string $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Theme>
     */
    public function getTheme(): Collection
    {
        return $this->theme;
    }

    public function addTheme(Theme $theme): static
    {
        if (!$this->theme->contains($theme)) {
            $this->theme->add($theme);
        }

        return $this;
    }

    public function removeTheme(Theme $theme): static
    {
        $this->theme->removeElement($theme);

        return $this;
    }

    /**
     * @return Collection<int, Diet>
     */
    public function getDiets(): Collection
    {
        return $this->diets;
    }

    public function addDiet(Diet $diet): static
    {
        if (!$this->diets->contains($diet)) {
            $this->diets->add($diet);
        }

        return $this;
    }

    public function removeDiet(Diet $diet): static
    {
        $this->diets->removeElement($diet);

        return $this;
    }

    /**
     * @return Collection<int, Dish>
     */
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
        }

        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        $this->dishes->removeElement($dish);

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        $this->pictures->removeElement($picture);

        return $this;
    }

    /**
     * @return Collection<int, OrderMenu>
     */
    public function getOrderMenus(): Collection
    {
        return $this->orderMenus;
    }

    public function addOrderMenu(OrderMenu $orderMenu): static
    {
        if (!$this->orderMenus->contains($orderMenu)) {
            $this->orderMenus->add($orderMenu);
            $orderMenu->setMenu($this);
        }

        return $this;
    }

    public function removeOrderMenu(OrderMenu $orderMenu): static
    {
        if ($this->orderMenus->removeElement($orderMenu)) {
            // Rien à faire ici
        }

        return $this;
    }
}
