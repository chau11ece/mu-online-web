USE MuOnline;
GO

-- Create 3 characters per account (30 total) with random classes, levels, stats, resets
DECLARE @accounts TABLE (id INT IDENTITY(1,1), memb_id VARCHAR(10));
INSERT INTO @accounts (memb_id) VALUES
('testuser'),('muuser02'),('muuser03'),('muuser04'),('muuser05'),
('muuser06'),('muuser07'),('muuser08'),('muuser09'),('muuser10');

DECLARE @classes TABLE (id INT IDENTITY(1,1), classId TINYINT, className VARCHAR(20), baseStr INT, baseDex INT, baseVit INT, baseEne INT, baseLead INT);
INSERT INTO @classes VALUES
(0,  'DarkWizard',    18, 18, 15, 30, 0),
(16, 'DarkKnight',    28, 20, 25, 10, 0),
(32, 'FairyElf',      22, 25, 20, 15, 0),
(48, 'MagicGladiator', 26, 26, 26, 26, 0),
(64, 'DarkLord',      26, 20, 20, 15, 25),
(80, 'Summoner',      21, 21, 18, 23, 0),
(96, 'RageFighter',   32, 27, 25, 20, 0);

-- Character names pool
DECLARE @names TABLE (id INT IDENTITY(1,1), charName VARCHAR(10));
INSERT INTO @names VALUES
('Arthas'),('Sylvanas'),('Illidan'),('Thrall'),('Jaina'),
('Varian'),('Anduin'),('Tyrande'),('Malfurion'),('Guldan'),
('Garrosh'),('Khadgar'),('Medivh'),('Lothar'),('Uther'),
('Ragnaros'),('Deathwing'),('Alexstra'),('Ysera'),('Nozdormu'),
('Cenarius'),('Sargeras'),('Archmond'),('Mannorth'),('Kiljaeden'),
('Drekthar'),('Rexxar'),('Cairne'),('Baine'),('Saurfang');

DECLARE @i INT = 1;
DECLARE @charIdx INT = 1;

WHILE @i <= 10
BEGIN
    DECLARE @memb VARCHAR(10);
    SELECT @memb = memb_id FROM @accounts WHERE id = @i;

    DECLARE @j INT = 1;
    WHILE @j <= 3
    BEGIN
        DECLARE @charName VARCHAR(10);
        SELECT @charName = charName FROM @names WHERE id = @charIdx;

        -- Random class
        DECLARE @classRow INT = (ABS(CHECKSUM(NEWID())) % 7) + 1;
        DECLARE @class TINYINT, @bStr INT, @bDex INT, @bVit INT, @bEne INT, @bLead INT;
        SELECT @class = classId, @bStr = baseStr, @bDex = baseDex, @bVit = baseVit, @bEne = baseEne, @bLead = baseLead
        FROM @classes WHERE id = @classRow;

        -- Random level 50-400, random resets 0-50
        DECLARE @level INT = (ABS(CHECKSUM(NEWID())) % 351) + 50;
        DECLARE @resets INT = (ABS(CHECKSUM(NEWID())) % 51);
        DECLARE @grandResets INT = (ABS(CHECKSUM(NEWID())) % 6);

        -- Stats scale with level and resets
        DECLARE @statPoints INT = (@level * 5) + (@resets * 500);
        DECLARE @str INT = @bStr + (ABS(CHECKSUM(NEWID())) % (@statPoints / 4 + 1));
        DECLARE @dex INT = @bDex + (ABS(CHECKSUM(NEWID())) % (@statPoints / 4 + 1));
        DECLARE @vit INT = @bVit + (ABS(CHECKSUM(NEWID())) % (@statPoints / 4 + 1));
        DECLARE @ene INT = @bEne + (ABS(CHECKSUM(NEWID())) % (@statPoints / 4 + 1));
        DECLARE @lead INT = CASE WHEN @class = 64 THEN @bLead + (ABS(CHECKSUM(NEWID())) % (@statPoints / 8 + 1)) ELSE 0 END;

        -- Cap stats at 32767
        SET @str = CASE WHEN @str > 32767 THEN 32767 ELSE @str END;
        SET @dex = CASE WHEN @dex > 32767 THEN 32767 ELSE @dex END;
        SET @vit = CASE WHEN @vit > 32767 THEN 32767 ELSE @vit END;
        SET @ene = CASE WHEN @ene > 32767 THEN 32767 ELSE @ene END;
        SET @lead = CASE WHEN @lead > 32767 THEN 32767 ELSE @lead END;

        -- Remaining level up points
        DECLARE @usedPoints INT = (@str - @bStr) + (@dex - @bDex) + (@vit - @bVit) + (@ene - @bEne) + (@lead - @bLead);
        DECLARE @levelUpPts INT = CASE WHEN @statPoints - @usedPoints > 0 THEN @statPoints - @usedPoints ELSE 0 END;

        -- Life/Mana based on vitality/energy
        DECLARE @maxLife REAL = 60.0 + (@vit * 2.0) + (@level * 1.5);
        DECLARE @maxMana REAL = 60.0 + (@ene * 1.5) + (@level * 1.0);

        -- Experience
        DECLARE @exp BIGINT = CAST(@level AS BIGINT) * CAST(@level AS BIGINT) * 100;

        -- Money (zen) - random 1M to 2B
        DECLARE @money INT = (ABS(CHECKSUM(NEWID())) % 2000000000) + 1000000;

        -- PK stats
        DECLARE @pkCount INT = ABS(CHECKSUM(NEWID())) % 50;
        DECLARE @pkLevel INT = CASE WHEN @pkCount > 10 THEN 4 WHEN @pkCount > 5 THEN 3 WHEN @pkCount > 0 THEN 2 ELSE 3 END;

        -- Duels
        DECLARE @winDuels INT = ABS(CHECKSUM(NEWID())) % 200;
        DECLARE @loseDuels INT = ABS(CHECKSUM(NEWID())) % 100;

        -- Master level (if level >= 400)
        DECLARE @mLevel INT = CASE WHEN @level >= 350 THEN (ABS(CHECKSUM(NEWID())) % 200) + 1 ELSE 0 END;
        DECLARE @mlPoint INT = @mLevel * 1;
        DECLARE @mlExp BIGINT = CAST(@mLevel AS BIGINT) * CAST(@mLevel AS BIGINT) * 1000;
        DECLARE @mlNextExp BIGINT = @mlExp + 100000;

        -- Random map spawn (Lorencia=0, Devias=2, Noria=3, Elbeland=51, Atlans=7)
        DECLARE @maps TABLE (mapNum SMALLINT, posX SMALLINT, posY SMALLINT);
        DELETE FROM @maps;
        INSERT INTO @maps VALUES (0, 130, 130), (2, 197, 35), (3, 169, 109), (7, 24, 19), (51, 50, 220);
        DECLARE @mapIdx INT = (ABS(CHECKSUM(NEWID())) % 5) + 1;
        DECLARE @mapNum SMALLINT, @mapX SMALLINT, @mapY SMALLINT;
        SELECT @mapNum = mapNum, @mapX = posX, @mapY = posY FROM (SELECT ROW_NUMBER() OVER (ORDER BY mapNum) AS rn, * FROM @maps) t WHERE rn = @mapIdx;

        -- HOF wins, SkyEvent wins
        DECLARE @hofWins INT = ABS(CHECKSUM(NEWID())) % 20;
        DECLARE @skyWins INT = ABS(CHECKSUM(NEWID())) % 10;

        -- Insert Character
        INSERT INTO Character (
            AccountID, Name, cLevel, LevelUpPoint, Class, Experience,
            Strength, Dexterity, Vitality, Energy, Leadership,
            Money, Life, MaxLife, Mana, MaxMana,
            MapNumber, MapPosX, MapPosY, MapDir,
            PkCount, PkLevel, PkTime, CtlCode,
            ChatLimitTime, FruitPoint, RESETS,
            mLevel, mlPoint, mlExperience, mlNextExp,
            InventoryExpansion, WinDuels, LoseDuels,
            PenaltyMask, ExGameServerCode,
            GrandResets, hof_wins, IsMarried, TotalTime,
            SkyEventWins, MDate, LDate
        ) VALUES (
            @memb, @charName, @level, @levelUpPts, @class, @exp,
            @str, @dex, @vit, @ene, @lead,
            @money, @maxLife, @maxLife, @maxMana, @maxMana,
            @mapNum, @mapX, @mapY, 1,
            @pkCount, @pkLevel, 0, 0,
            0, 0, @resets,
            @mLevel, @mlPoint, @mlExp, @mlNextExp,
            0, @winDuels, @loseDuels,
            0, 0,
            @grandResets, @hofWins, 0, (ABS(CHECKSUM(NEWID())) % 50000),
            @skyWins, GETDATE(), GETDATE()
        );

        -- Update AccountCharacter slot
        IF @j = 1
            UPDATE AccountCharacter SET GameID1 = @charName WHERE Id = @memb;
        ELSE IF @j = 2
            UPDATE AccountCharacter SET GameID2 = @charName WHERE Id = @memb;
        ELSE IF @j = 3
            UPDATE AccountCharacter SET GameID3 = @charName WHERE Id = @memb;

        SET @charIdx = @charIdx + 1;
        SET @j = @j + 1;
    END

    -- Set first char as current
    UPDATE AccountCharacter SET GameIDC = (SELECT GameID1 FROM AccountCharacter WHERE Id = @memb) WHERE Id = @memb;

    SET @i = @i + 1;
END

-- Also add MEMB_STAT entries and credits for each account
DECLARE @k INT = 1;
WHILE @k <= 10
BEGIN
    DECLARE @mid VARCHAR(10);
    SELECT @mid = memb_id FROM @accounts WHERE id = @k;

    IF NOT EXISTS (SELECT 1 FROM MEMB_STAT WHERE memb___id = @mid)
        INSERT INTO MEMB_STAT (memb___id, ConnectStat, ServerName, IP, ConnectTM, DisConnectTM, TotalTime)
        VALUES (@mid, 0, 'Server1', '192.168.1.' + CAST((ABS(CHECKSUM(NEWID())) % 254) + 1 AS VARCHAR), GETDATE(), GETDATE(), ABS(CHECKSUM(NEWID())) % 50000);

    IF NOT EXISTS (SELECT 1 FROM MEMB_CREDITS WHERE memb___id = @mid)
        INSERT INTO MEMB_CREDITS (memb___id, credits) VALUES (@mid, (ABS(CHECKSUM(NEWID())) % 10000));

    SET @k = @k + 1;
END

-- Verify
SELECT Name, AccountID, cLevel, Class, Strength, Dexterity, Vitality, Energy, RESETS, GrandResets, Money, WinDuels, mLevel
FROM Character ORDER BY cLevel DESC;
GO
