<?php
// Simuler l'authentification API : validation du token, réponses success/failure, remember me.
// Couvrir les cas d'entêtes absentes, de token expiré et de gestion des exceptions.

// TODO: testAuthenticateReturnsPassportWhenValidTokenProvided.
// TODO: testAuthenticateThrowsCustomExceptionWhenHeaderMissing.
// TODO: testOnAuthenticationFailureReturnsJsonResponseWith401.