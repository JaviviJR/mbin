<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\RelatedLinkDTO;
use App\DTO\UserDto;
use App\DTO\UserSmallResponseDto;
use App\Entity\User;
use App\Repository\InstanceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UserFactory
{
    public function __construct(
        private readonly ImageFactory $imageFactory,
        private readonly InstanceRepository $instanceRepository,
        private readonly Security $security,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function createDto(User $user): UserDto
    {
//        $temp = $this->denormalizer->denormalize($user->getRelatedLinks(), sprintf('%s[]', RelatedLinkDTO::class));

        $dto = UserDto::create(
            $user->username,
            $user->email,
            $user->avatar ? $this->imageFactory->createDto($user->avatar) : null,
            $user->cover ? $this->imageFactory->createDto($user->cover) : null,
            $user->about,
            $user->createdAt,
            $user->fields,
            $user->apId,
            $user->apProfileId,
            $user->getId(),
            $user->followersCount,
            'Service' === $user->type, // setting isBot
            $user->isAdmin(),
            $user->isModerator(),
//            $user->relatedLinks,
            $this->denormalizer->denormalize($user->getRelatedLinks(), sprintf('%s[]', RelatedLinkDTO::class)),
        );

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        // Only return the user's vote if permission to control voting has been given
        $dto->isFollowedByUser = $this->security->isGranted('ROLE_OAUTH2_USER:FOLLOW') ? $currentUser->isFollowing($user) : null;
        $dto->isFollowerOfUser = $this->security->isGranted('ROLE_OAUTH2_USER:FOLLOW') && $user->showProfileFollowings ? $user->isFollowing($currentUser) : null;
        $dto->isBlockedByUser = $this->security->isGranted('ROLE_OAUTH2_USER:BLOCK') ? $currentUser->isBlocked($user) : null;

        $instance = $this->instanceRepository->getInstanceOfUser($user);
        if ($instance) {
            $dto->serverSoftware = $instance->software;
            $dto->serverSoftwareVersion = $instance->version;
        }

        return $dto;
    }

    public function createSmallDto(User|UserDto $user): UserSmallResponseDto
    {
        $dto = $user instanceof User ? $this->createDto($user) : $user;

        return new UserSmallResponseDto($dto);
    }

    public function createDtoFromAp($apProfileId, $apId): UserDto
    {
        $dto = (new UserDto())->create('@'.$apId, $apId, null, null, null, null, null, $apId, $apProfileId);
        $dto->plainPassword = bin2hex(random_bytes(20));

        return $dto;
    }
}
