<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Data\Exception\InvalidEntityException;

class AddGroupNicknames extends Migration {

    /**
     * Run the migrations.
     * @throws InvalidEntityException
     */
    public function up() {
        $repository = app(GroupRepositoryInterface::class);
        foreach($repository->all() as $group) {
            $group->setNickname(Str::camel($group->getName()));
            $repository->persist($group, false);
        }
        $repository->flush();
    }

    /**
     * Reverse the migrations.
     * @throws InvalidEntityException
     */
    public function down() {
        $repository = app(GroupRepositoryInterface::class);
        foreach($repository->all() as $group) {
            $group->setNickname('');
            $repository->persist($group, false);
        }
        $repository->flush();
    }
}
