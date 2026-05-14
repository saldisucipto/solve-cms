<?php

namespace App\Users\Services;

use App\Users\Repositories\UserRepository;
use InvalidArgumentException;

class UserService
{
    public function __construct(protected UserRepository $repository)
    {
    }

    public function list(): array
    {
        return $this->repository->all();
    }

    public function find(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function create(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = trim((string) ($input['password'] ?? ''));
        $confirmPassword = trim((string) ($input['password_confirm'] ?? ''));

        if ($name === '' || $email === '' || $password === '') {
            throw new InvalidArgumentException('Nama, email, dan password wajib diisi.');
        }

        if ($password !== $confirmPassword) {
            throw new InvalidArgumentException('Password tidak cocok.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        if ($this->repository->findByEmail($email) !== null) {
            throw new InvalidArgumentException('Email sudah terdaftar.');
        }

        $userId = $this->repository->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        if (isset($input['role_id']) && !empty($input['role_id'])) {
            $this->repository->assignRole($userId, (int) $input['role_id']);
        }

        return $userId;
    }

    public function update(int $id, array $input): void
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new InvalidArgumentException('User tidak ditemukan.');
        }

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($name === '' || $email === '') {
            throw new InvalidArgumentException('Nama dan email wajib diisi.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        if ($email !== $user['email']) {
            if ($this->repository->findByEmail($email) !== null) {
                throw new InvalidArgumentException('Email sudah terdaftar oleh user lain.');
            }
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
        ];

        if (isset($input['password']) && !empty(trim((string) $input['password']))) {
            $password = trim((string) $input['password']);
            $confirmPassword = trim((string) ($input['password_confirm'] ?? ''));

            if ($password !== $confirmPassword) {
                throw new InvalidArgumentException('Password tidak cocok.');
            }

            $updateData['password'] = $password;
        }

        $this->repository->update($id, $updateData);

        if (isset($input['role_id']) && !empty($input['role_id'])) {
            $this->repository->assignRole($id, (int) $input['role_id']);
        }
    }

    public function delete(int $id): void
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new InvalidArgumentException('User tidak ditemukan.');
        }

        $this->repository->delete($id);
    }

    public function getRoles(): array
    {
        return $this->repository->getRoles();
    }
}
