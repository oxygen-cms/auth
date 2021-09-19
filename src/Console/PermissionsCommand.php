<?php

namespace Oxygen\Auth\Console;

use Oxygen\Auth\Permissions\PermissionsSource;
use Oxygen\Auth\Permissions\TreePermissionsSystem;
use Oxygen\Auth\Entity\Group;
use Oxygen\Auth\Permissions\Permissions;
use Oxygen\Auth\Permissions\PermissionsExplanation;
use Oxygen\Auth\Repository\GroupRepositoryInterface;
use Oxygen\Core\Console\Command;
use Oxygen\Data\Exception\InvalidEntityException;
use Oxygen\Data\Exception\NoResultException;

class PermissionsCommand extends Command {

    /**
     * @var string name and signature of console command
     */
    protected $signature = 'permissions {nickname? : the nickname of the Group}
        {--g|grant=* : Grant this group the specified permission}
        {--d|deny=* : Deny this group the specified permission}
        {--u|unset=* : Revert this permission to a default value}
        {--inherit=* : Inherits permissions from a given contentType from another contentType}
        {--a|all : Lists all permissions, even inherited ones}
        {--dry-run : Do not save any modifications to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with group permissions';

    const PERMISSIONS_BY_GROUP_HEADERS = [
        'Group Id',
        'Group Name',
        'Previous Value',
        'New Value'
    ];

    private GroupRepositoryInterface $groups;
    private Permissions $permissions;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GroupRepositoryInterface $groups, Permissions $permissions) {
        parent::__construct();
        $this->groups = $groups;
        $this->permissions = $permissions;
    }

    /**
     * Display users in a table.
     *
     * @throws InvalidEntityException
     */
    public function handle() {
        $allGroupsGenerator = function() {
            foreach($this->groups->all() as $group) {
                yield $group;
            }
        };
        $keys = $this->getKeyArguments();
        if($keys === null) { return 1; }

        $group = null;
        if($this->argument('nickname') !== null) {
            try {
                $group = $this->groups->findByNickname($this->argument('nickname'));
                $nickname = $group->getNickname();
                $this->info("Selected group $nickname");
            } catch(NoResultException $e) {
                $this->warn('Group `' . $this->argument('nickname') . '` not found. Aborting');
                return;
            }
        } else {
            $this->warn('No group was specified, operating on <fg=yellow;options=bold>all</> groups at once');
            $this->line('<info>Tip:</info> use `artisan permissions [groupNickname]` to administer permissions for a single group');
        }

        $previousPermissionValues = [];

        if(!empty($keys) !== null) {
            foreach($keys as $key) {
                $recognised = $this->isPermissionRecognised($key);
                if(!$recognised['recognised'] && $this->option('unset') === null &&
                    !$this->confirm("Key `$key` unrecognised, do you wish to continue?")) {
                    return;
                }
                $previousPermissionValues[$key] = $this->cachePreviousPermissionValues($key);
            }
        }

        foreach($this->option('grant') as $key) {
            $this->grantPermissions($group, $key);
        }
        foreach($this->option('unset') as $key) {
            $this->unsetPermissions($group, $key);
        }
        foreach($this->option('deny') as $key) {
            $this->denyPermissions($group, $key);
        }
        foreach($this->option('inherit') as $inheritance) {
            list($contentType, $parentContentType) = explode(':', $inheritance);
            if ($parentContentType === 'null') {
                $parentContentType = null;
            }
            $this->setPermissionInheritance($group, $contentType, $parentContentType);
            $this->info("Updating _parent for `${contentType}`...");
        }

        if(empty($keys)) {
            $all = $this->option('all');
            if($group === null) {
                $all = true;
            }

            $groupsGenerator = function() use($group) {
                $source = $group;
                while($source !== null) {
                    yield $source;
                    $source = $source->getParent();
                }
            };

            $this->info("\n\nContent Types:");
            $this->renderInheritanceTable($group !== null ? $groupsGenerator : $allGroupsGenerator, $group, $all);
            $this->info("\n\nPermissions:");
            $this->renderPermissionRows($group !== null ? $groupsGenerator : $allGroupsGenerator, $group, $all);
            return;
        } else {
            $changed = $this->renderPermissionsTable($keys, $allGroupsGenerator, $previousPermissionValues, 'key');
            if(!$changed) {
                $this->warn('No changes made!');
            }
        }

        if($this->option('dry-run')) {
            return;
        }

        if(!$this->confirm('Would you like to save this configuration?', true)) {
            $this->error('Aborted.');
            return;
        }

        $this->saveGroups($group);
    }

    private function getKeyArguments(): ?array {
        $keys = [];
        if($this->option('grant') !== null) { $keys = array_merge($keys, $this->option('grant')); }
        if($this->option('deny') !== null) { $keys = array_merge($keys, $this->option('deny')); }
        if($this->option('unset') !== null) { $keys = array_merge($keys, $this->option('unset')); }
        if($this->option('inherit') !== null) {
            foreach ($this->option('inherit') as $key) {
                $parts = explode(':', $key);
                if(count($parts) !== 2) {
                    $this->line('<fg=red>Expected argument <fg=white;options=bold,underscore>--inherit=[contentType]:[parentContentType]</>, got <fg=white;options=bold,underscore>--inherit=' . $key . '</> instead.</>');
                    return null;
                }
                list($from, $to) = $parts;
                $keys[] = $from;
            }
        }
        return $keys;
    }

    private function renderInheritanceTable(callable $groupsGenerator, ?Group $specificGroup, bool $all) {
        $contentTypes = [];
        if($specificGroup !== null) {
            $contentTypes = array_merge($contentTypes, $specificGroup->getPermissionContentTypes());
        }
        if($all) {
            $contentTypes = array_merge($contentTypes, $this->permissions->getAllContentTypes());
        }
        sort($contentTypes);
        $contentTypes = array_unique($contentTypes);

        $this->renderPermissionsTable($contentTypes, $groupsGenerator, null, 'Content Type');
    }

    private function renderPermissionRows(callable $groupsGenerator, ?Group $specificGroup, bool $all) {
        $keys = [];
        if($specificGroup !== null) {
            $keys = array_merge($keys, $specificGroup->getFlatPermissions());
        }
        if($all) {
            $keys = array_merge($keys, $this->permissions->getAllPermissions());
        }
        sort($keys);
        $keys = array_unique($keys);

        $this->renderPermissionsTable($keys, $groupsGenerator, null, 'Key');
    }

    private function renderPermissionsTable(array $keys, callable $groupsGenerator, ?array $previousPermissionValues = null, string $firstColumnName) {
        $permissionsRows = array_map(function(string $key) use($groupsGenerator, $previousPermissionValues) {
            $row = [$key];
            $groups = $groupsGenerator();
            foreach($groups as $source) {
                $explanation = $this->explain($source, $key);

                if($previousPermissionValues !== null ){
                    $before = $previousPermissionValues[$key][$source->getId()];
                    if($before->equals($explanation)) {
                        $row[] = $explanation->toConsoleString();
                    } else {
                        $row[] = $before->toConsoleString() . '  🠲   ' . $explanation->toConsoleString();
                    }
                } else {
                    $row[] = $explanation->toConsoleString();
                }
            }

            $recognised = $this->isPermissionRecognised($key);
            $row[] = $recognised['reason'];
            return $row;
        }, $keys);

        $this->table($this->getPermissionsHeaders($groupsGenerator, $firstColumnName), $permissionsRows, 'box-double');
    }

    private function cachePreviousPermissionValues(string $key): array {
        $cache = [];
        foreach($this->groups->all() as $group) {
            $explanation = $this->explain($group, $key);
            $cache[$group->getId()] = $explanation;
        }
        return $cache;
    }

    private function explain(Group $source, string $key) {
        if(count(explode('.', $key)) === 1) {
            return $this->permissions->explainParentForGroup($source, $key);
        } else {
            return $this->permissions->explainForGroup($source, $key);
        }
    }

    private function isPermissionRecognised(string $key): array {
        $parts = explode('.', $key);
        if(count($parts) === 1) {
            return [ 'recognised' => true, 'reason' => ''];
        }

        list($contentType, $action) = $parts;
        $allActions = $this->permissions->getAllActions();

        if(str_starts_with($contentType, '_')) {
            if(!isset($allActions[$action])) {
                return [ 'recognised' => false, 'reason' => '<fg=yellow>no action named <options=underscore>' . $action . '</> for any content-type</>'];
            }
        } else if(!in_array($key, $this->permissions->getAllPermissions())) {
            return [ 'recognised' => false, 'reason' => '<fg=yellow>unrecognised key</>' ];
        }

        return [ 'recognised' => true, 'reason' => ''];
    }

    /**
     * @param callable $groupsGenerator
     * @return string[]
     */
    private function getPermissionsHeaders(callable $groupsGenerator, string $firstColumn): array {
        $headers = [$firstColumn];
        $groups = $groupsGenerator();
        foreach ($groups as $source) {
            $headers[] = $source->getNickname() . ' (' . $source->getName() . ')';
        }
        $headers[] = 'Warnings';
        return $headers;
    }

    private function grantPermissions(?Group $group, ?string $key) {
        if($group === null) {
            $this->warn('No group was specified, granting to all groups');
        }
        $groups = $group !== null ? [$group] : $this->groups->all();
        foreach($groups as $group) {
            $group->grantPermissions($key);
        }
        $this->info("Granting <options=bold>${key}</>");
    }

    private function denyPermissions(?Group $group, ?string $key) {
        $groups = $group !== null ? [$group] : $this->groups->all();
        foreach($groups as $group) {
            $group->denyPermissions($key);
        }
        $this->info("Denying <options=bold>${key}</>");
    }

    private function unsetPermissions(?Group $group, ?string $key) {
        $groups = $group !== null ? [$group] : $this->groups->all();
        foreach($groups as $group) {
            $group->unsetPermissions($key);
        }
        $this->info("Unsetting <options=bold>${key}</>");
    }

    private function setPermissionInheritance(?Group $group, string $contentType, ?string $parentContentType) {
        $groups = $group !== null ? [$group] : $this->groups->all();
        foreach($groups as $group) {
            $group->setPermissionInheritance($contentType, $parentContentType);
        }
    }

    /**
     * @param Group|null $group
     * @throws InvalidEntityException
     */
    private function saveGroups(?Group $group): void {
        if ($group !== null) {
            $this->groups->persist($group);
            $this->info('Group saved.');
        } else {
            foreach ($this->groups->all() as $group) {
                $this->groups->persist($group, false);
            }
            $this->groups->flush();
            $this->info('All groups saved.');
        }
    }

}