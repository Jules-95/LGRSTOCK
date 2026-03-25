--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `magasin`, `created_at`) VALUES
(1, 'employe', '$2y$12$X4V5VhhrFbM0r7j4y2d63uc/anI9CuFnk5C.OcGQteT.jwGgvYheu', 'employe', 'tours_nord', '2026-03-25 12:09:47'),
(2, 'admin_nord', '$2y$12$ofIhJvWlOQF3PJE.MOioZuLpubgHJKjnkq2f1y9zsNcyuePseNMba', 'admin', 'tours_nord', '2026-03-25 12:09:47'),
(3, 'admin_centre', '$2y$12$6n5I82uhH/MKvUHil6f9Ke12g5/NfgOqJtjAuKxuKjhSgReRlRAKy', 'admin', 'tours_centre', '2026-03-25 12:09:47');
