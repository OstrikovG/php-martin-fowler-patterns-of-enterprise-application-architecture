CREATE TABLE product(
    id INTEGER PRIMARY KEY NOT NULL,
    description TEXT,
    stock FLOAT,
    cost_price FLOAT,
    sale_price FLOAT,
    bar_code TEXT,
    date_register DATE,
    origin CHAR(1)
);