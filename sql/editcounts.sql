CREATE TABLE edit_counts (
    ec_user INTEGER UNSIGNED NOT NULL,
    ec_property VARBINARY(255) NOT NULL,
    ec_value INTEGER UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(ec_user,ec_property)
);
