set schema 'sae_db';

-- vérifie que les insertions dans la table compte respectent les contraintes de clés privée
CREATE OR REPLACE FUNCTION ftg_verifier_cles_compte() RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'PAS BIENG !';
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER tg_verifier_cles_compte
BEFORE INSERT ON _compte
FOR EACH ROW
EXECUTE FUNCTION verifier_cles_compte();


-- vérifie que l'email est valide
CREATE OR REPLACE FUNCTION verifier_email_connexion(email_input VARCHAR)
RETURNS TEXT AS $$
DECLARE
    email_count INT;
BEGIN
        -- Vérifier si l'email existe dans la table _compte
    SELECT COUNT(*) INTO email_count
    FROM _compte
    WHERE _compte.email = email_input;

    -- Si l'email existe
    IF email_count > 0 THEN
        RETURN 'Email valide et existant';
    ELSE
        RETURN 'Email non trouvé dans la base';
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Mise à jour de automatique de la date de mise à jour des offres
CREATE OR REPLACE FUNCTION update_offer_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.date_mise_a_jour = CURRENT_DATE;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER offer_update_timestamp
BEFORE UPDATE ON _offre
FOR EACH ROW
EXECUTE FUNCTION update_offer_timestamp();

-- mise à jour du statut 'en ligne' de l'offre
CREATE OR REPLACE FUNCTION trigger_log_changement_statut()
RETURNS TRIGGER AS $$
BEGIN
    -- Enregistrement de la date de changement de statut
    INSERT INTO _log_changement_status (id_offre, date_changement)
    VALUES (NEW.id_offre, CURRENT_DATE);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER log_changement_statut
AFTER UPDATE ON _offre
FOR EACH ROW
WHEN (OLD.est_en_ligne IS DISTINCT FROM NEW.est_en_ligne)
EXECUTE FUNCTION trigger_log_changement_statut();
