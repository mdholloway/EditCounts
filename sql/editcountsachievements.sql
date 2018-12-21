CREATE TABLE edit_counts_achievements (
    eca_user INTEGER UNSIGNED NOT NULL,
    eca_property VARBINARY(255) NOT NULL,
    PRIMARY KEY(eca_user,eca_property)
);
