<?php

namespace App\Model\Facades;

use App\Model\Entities\Role;
use App\Model\Repositories\RoleRepository;

/**
 * Class RolesFacade - fasáda pro využívání rolí z presenterů
 * @package App\Model\Facades
 */
class RolesFacade
{
    /** @var RoleRepository $roleRepository */
    private /*RoleRepository*/
        $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Metoda pro načtení jedné role
     * @param int $id
     * @return Role
     * @throws \Exception
     */
    public function getRole(int $id): Role
    {
        return $this->roleRepository->find($id); //buď počítáme s možností vyhození výjimky, nebo ji ošetříme už tady a můžeme vracet např. null
    }

    /**
     * Metoda pro vyhledání rolí
     * @return Role[]
     */
    public function findRoles(): array
    {
        return $this->roleRepository->findAll();
    }
}
