<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;
use App\Models\Announcement;

class FavoriteService
{
    public function __construct(protected FavoriteRepository $repo) {}

    public function getUserImageFavorites($user)
    {
        return $this->repo->getUserImageFavorites($user);
    }

    public function getUserFileFavorites($user)
    {
        return $this->repo->getUserFileFavorites($user);
    }

    public function addFavorite($user, Announcement $announcement)
    {
        return $this->repo->addFavorite($user, $announcement);
    }

    public function removeFavorite($user, Announcement $announcement)
    {
        return $this->repo->removeFavorite($user, $announcement);
    }
}
