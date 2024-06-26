-- #!mysql
-- #{ players
    -- #{ init
        CREATE TABLE IF NOT EXISTS players (
            xuid VARCHAR(128) PRIMARY KEY,
            player_name VARCHAR(16),
            friends_list VARCHAR(1024),
            invitations_received VARCHAR(1024),
            invitations_sent VARCHAR(1024),
            `limit` INT DEFAULT 10
        );
    -- # }
    -- #{ add
        -- # :xuid string
        -- # :player_name string
        INSERT IGNORE INTO players(xuid, player_name) VALUES (:xuid, :player_name);
    -- #}

    -- #{ get
        -- # :player_name string
        SELECT * FROM players WHERE player_name= :player_name;
    -- #}

    -- #{ getFriends
        -- # :name string
        SELECT friends_list FROM players WHERE player_name = :name;
    -- #}

    -- #{ getReceived
        -- # :name string
        SELECT invitations_received FROM players WHERE player_name = :name;
    -- #}

    -- #{ getSent
        -- # :name string
        SELECT invitations_sent FROM players WHERE player_name = :name;
    -- #}

    -- #{ setFriends
        -- # :ret string
        -- # :name string
        UPDATE players SET friends_list = :ret WHERE player_name = :name;
    -- #}

    -- #{ setReceived
        -- # :ret string
        -- # :name string
        UPDATE players SET invitations_received = :ret WHERE player_name = :name;
    -- #}

    -- #{ setSent
        -- # :ret string
        -- # :name string
        UPDATE players SET invitations_sent = :ret WHERE player_name = :name;
    -- #}

-- #}