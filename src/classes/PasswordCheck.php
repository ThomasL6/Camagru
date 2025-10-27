<?php

class PasswordCheck {
	private const MIN_LENGTH = 8;
	public static function isValid(string $password): array{
		$rules = [
			'length' => [
				'check' => fn($pwd) => strlen($pwd) >= self::MIN_LENGTH,
				'message' => 'Password must be at least ' . self::MIN_LENGTH . ' characters long.'
			],
			'uppercase' => [
				'check' => fn($pwd) => preg_match('/[A-Z]/', $pwd),
				'message' => 'Password must contain at least one uppercase letter.'
			],
			'lowercase' => [
				'check' => fn($pwd) => preg_match('/[a-z]/', $pwd),
				'message' => 'Password must contain at least one lowercase letter.'
			],
			'digit' => [
				'check' => fn($pwd) => preg_match('/[0-9]/', $pwd),
				'message' => 'Password must contain at least one digit.'
			],
			'special' => [
				'check' => fn($pwd) => preg_match('/[\W_]/', $pwd),
				'message' => 'Password must contain at least one special character.'
			]
		];

		$errors = [];
        foreach ($rules as $rule) {
            if (!$rule['check']($password)) {
                $errors[] = $rule['message'];
            }
        }
		return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
	}
}