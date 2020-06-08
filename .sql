
CREATE OR REPLACE TABLE IDENTITY(

    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100),
    surname VARCHAR(100),
    birthdate DATE,
)

CREATE OR REPLACE TABLE USER(

    id INT NOT NULL AUTO_INCREMENT,
    login VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL;
    statut INT NOT NULL;
    right VARCHAR(255) NOT NULL;
    identity_id INT,
    FOREIGN KEY identity_id REFERENCES IDENTITY(id) ON DELETE CASCADE

)

CREATE OR REPLACE TABLE IMAGE(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255),
    REFERENCE VARCHAR (20),
    uploading_date TIMESTAMP,

)

CREATE OR REPLACE TABLE COMPONNENT(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255),
    class VARCHAR(255),
    type_Componnent INT, 
    color INT,

 )

CREATE OR REPLACE TABLE PAGE(

    id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100),
    gabarit INT NOT NULL,
    creation_date TIMESTAMP,
    theme INT,
    background_image INT
    FOREIGN KEY background_image REFERENCES IMAGE(id) ON DELETE CASCADE,

)

CREATE OR REPLACE TABLE ARTICLE(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    subtiltle VARCHAR(255),
    creation_date TIMESTAMP,
    user_id INT NOT NULL,
    FOREIGN KEY user_id REFERENCES USER(id) ON DELETE CASCADE

)

CREATE OR REPLACE TABLE ARTICLE_IMAGE(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255),
    REFERENCE VARCHAR (2),
    uploading_date TIMESTAMP,
    article_id INT NOT NULL,
    image_id INT NOT NULL,
    FOREIGN KEY article_id REFERENCES ARTICLE(id) ON DELETE CASCADE
    FOREIGN KEY image_id REFERENCES IMAGE(id) ON DELETE CASCADE

)

CREATE OR REPLACE TABLE COMMENT(

    id INT NOT NULL AUTO_INCREMENT,
    comment TEXT,
    post_date TIMESTAMP,
    user_id INT NOT NULL,
    target INT NOT NULL,
    target_type INT NOT NULL,
    FOREIGN KEY user_id REFERENCES USER(id) ON DELETE CASCADE

)

 CREATE OR REPLACE TABLE COMPONNENT_PAGE(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255),
    position INT,
    page_id INT NOT NULL,
    componnent_id INT NOT NULL,
    FOREIGN KEY page_id REFERENCES PAGE(id) ON DELETE CASCADE,
    FOREIGN KEY componnent_id REFERENCES COMPONNENT(id) ON DELETE CASCADE
 
 )

CREATE OR REPLACE TABLE MOVIE(

    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255),
    release DATE,
    duration TIME,
    synopsis TEXT,

)

CREATE OR REPLACE TABLE ROOM(

    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255),
    section CHAR(1),
    
)

CREATE OR REPLACE TABLE MOVIE_SESSION(
    id INT NOT NULL AUTO_INCREMENT,
    date_screaning DATE,
    sheldule TIME,
    movie_id INT,
    room_id INT,
    FOREIGN KEY movie_id REFERENCES MOVIE(id) ON DELETE CASCADE,
    FOREIGN KEY room_id REFERENCES ROOM(id) ON DELETE CASCADE
)