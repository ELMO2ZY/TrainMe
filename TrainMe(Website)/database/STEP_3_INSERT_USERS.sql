-- STEP 3: Insert default users
-- Make sure you're connected to trainme_db database!

INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@trainme.com', '$2y$10$1by7E3e11oQ2mhfPSGLfd.2GRD79akVma4xnUOgK51OmcI4aPxiJO', 'admin')
ON DUPLICATE KEY UPDATE email=email;

INSERT INTO users (name, email, password, role) 
VALUES ('Employee User', 'employee@trainme.com', '$2y$10$pmOPf395cl4p9ipuF9.UYuv3MuE3LWVMIMQ6mJG8wr4/dkHdcAFiW', 'employee')
ON DUPLICATE KEY UPDATE email=email;

