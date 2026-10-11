CREATE TABLE IF NOT EXISTS "#__compacttables_tables" (
  "id" serial NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "has_header" smallint DEFAULT 1 NOT NULL,
  "num_cols" smallint DEFAULT 0 NOT NULL,
  "num_rows" smallint DEFAULT 0 NOT NULL,
  "data" text NOT NULL,
  "params" text NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
