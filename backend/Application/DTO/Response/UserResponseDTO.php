<?php
/**
 * DTO de réponse pour un utilisateur/client
 */
class UserResponseDTO {
    public ?int $id;
    public string $email;
    public string $name;
    public string $forename;
    public string $type; // 'customer' ou 'owner'
    public ?string $createdAt;

    public function __construct(
        ?int $id,
        string $email,
        string $name,
        string $forename,
        string $type,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->forename = $forename;
        $this->type = $type;
        $this->createdAt = $createdAt;
    }

    public static function fromUser(User $user): self {
        $type = $user instanceof Customer ? 'customer' : 'owner';

        return new self(
            $user->getId(),
            $user->getEmail(),
            $user->getName(),
            $user->getForename(),
            $type,
            null
        );
    }

    public static function fromCustomer(Customer $customer): self {
        return new self(
            $customer->getId(),
            $customer->getEmail(),
            $customer->getName(),
            $customer->getForename(),
            'customer',
            null
        );
    }

    public static function fromOwner(Owner $owner): self {
        return new self(
            $owner->getId(),
            $owner->getEmail(),
            $owner->getName(),
            $owner->getForename(),
            'owner',
            null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'forename' => $this->forename,
            'type' => $this->type,
            'createdAt' => $this->createdAt,
        ];
    }
}
