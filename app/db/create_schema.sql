DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS pastes;

-- CodeIgniter Session table
CREATE TABLE sessions (
	session_id CHARACTER VARYING(40) PRIMARY KEY DEFAULT '0',
	ip_address CHARACTER VARYING(45) DEFAULT '0' NOT null,
	user_agent CHARACTER VARYING(120) NOT null,
	last_activity NUMERIC(10) DEFAULT 0 NOT null,
	user_data TEXT NOT null
);
CREATE INDEX last_activity_idx ON sessions (last_activity);

DROP FUNCTION IF EXISTS save_session(p_session_id CHARACTER VARYING(40), p_ip_address CHARACTER VARYING(45), p_user_agent CHARACTER VARYING(120), p_last_activity NUMERIC(10), p_user_data TEXT);
CREATE OR REPLACE FUNCTION save_session(
		p_session_id CHARACTER VARYING(40),
		p_ip_address CHARACTER VARYING(45),
		p_user_agent CHARACTER VARYING(120),
		p_last_activity NUMERIC(10),
		p_user_data TEXT
) RETURNS VOID AS $$
BEGIN
	LOOP
        UPDATE sessions SET ip_address = p_ip_address, user_agent = p_user_agent, last_activity = p_last_activity, user_data = p_user_data WHERE session_id = p_session_id;
        IF found THEN
            RETURN;
        END IF;
        BEGIN
            INSERT INTO sessions (session_id, ip_address, user_agent, last_activity, user_data) VALUES (p_session_id, p_ip_address, p_user_agent, p_last_activity, p_user_data);
            RETURN;
        EXCEPTION WHEN unique_violation THEN
        
        END;
    END LOOP;
END;
$$ LANGUAGE plpgsql;

CREATE TABLE pastes (
	id BIGSERIAL PRIMARY KEY,
	title CHARACTER VARYING(255) DEFAULT null,
	lang CHARACTER VARYING(255) NOT null DEFAULT 'ace/mode/text',
	data TEXT,
	previous_id BIGINT DEFAULT null,
	created_at TIMESTAMP NOT null DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT null DEFAULT CURRENT_TIMESTAMP
);
