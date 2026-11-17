<?php

namespace Application\GraphQL;

use Application\MessageBus\CommandBus;
use DateTime;
use Domain\Collection\DinosaursCollection;
use Domain\Exception\DinosaurNotFoundException;
use Domain\Model\Dinosaur;
use Domain\UseCase\CreateDinosaur\Input;
use Exception;
use Symfony\Component\Security\Core\Security;

final readonly class DinosaurResolver
{
    public function __construct(
        private DinosaursCollection $dinosaurs,
        private CommandBus          $commandBus,
        private Security            $security
    )
    {
    }

    public function dinosaurs(?string $search): array
    {
            $this->security->getUser()
            ?? throw new Exception('probleme');
        return $this->dinosaurs->search($search);
    }

    public function dinosaur(string $id): Dinosaur
    {
        return $this->dinosaurs->find($id)
            ?? throw new DinosaurNotFoundException($id);
    }

    public function createDinosaur(array|\ArrayObject $input): Dinosaur
    {
        $command = new input(
            $input['name'],
            $input['gender'],
            $input['speciesId'],
            $input['age'],
            new \DateTime($input['birthDate']),
            $input['eyesColor']
        );

        $output = $this->commandBus->dispatch($command);
        return $output->dinosaur;
    }
}
