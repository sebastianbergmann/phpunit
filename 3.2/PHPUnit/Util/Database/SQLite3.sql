--
-- PHPUnit
--
-- Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
-- All rights reserved.
--
-- Redistribution and use in source and binary forms, with or without
-- modification, are permitted provided that the following conditions
-- are met:
--
--   * Redistributions of source code must retain the above copyright
--     notice, this list of conditions and the following disclaimer.
-- 
--   * Redistributions in binary form must reproduce the above copyright
--     notice, this list of conditions and the following disclaimer in
--     the documentation and/or other materials provided with the
--     distribution.
--
--   * Neither the name of Sebastian Bergmann nor the names of his
--     contributors may be used to endorse or promote products derived
--     from this software without specific prior written permission.
--
-- THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
-- "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
-- LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
-- FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
-- COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
-- INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
-- BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
-- LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
-- CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
-- LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
-- ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
-- POSSIBILITY OF SUCH DAMAGE.
--
-- $Id$
--

CREATE TABLE IF NOT EXISTS run(
  run_id      INTEGER PRIMARY KEY AUTOINCREMENT,
  timestamp   INTEGER,
  revision    INTEGER,
  information STRING
);

CREATE TABLE IF NOT EXISTS test(
  run_id              INTEGER,
  test_id             INTEGER PRIMARY KEY AUTOINCREMENT,
  test_name           TEXT,
  test_result         INTEGER DEFAULT 0,
  test_message        TEXT    DEFAULT "",
  test_execution_time REAL    DEFAULT 0,
  code_method_id      INTEGER,
  node_root           INTEGER,
  node_left           INTEGER,
  node_right          INTEGER
);

CREATE INDEX IF NOT EXISTS test_run_id         ON test (run_id);
CREATE INDEX IF NOT EXISTS test_result         ON test (test_result);
CREATE INDEX IF NOT EXISTS test_code_method_id ON test (code_method_id);
CREATE INDEX IF NOT EXISTS test_node_root      ON test (node_root);
CREATE INDEX IF NOT EXISTS test_node_left      ON test (node_left);
CREATE INDEX IF NOT EXISTS test_node_right     ON test (node_right);

CREATE TABLE IF NOT EXISTS code_file(
  code_file_id   INTEGER PRIMARY KEY AUTOINCREMENT,
  code_file_name TEXT,
  code_file_md5  TEXT,
  revision       INTEGER
);

CREATE TABLE IF NOT EXISTS code_class(
  code_file_id          INTEGER,
  code_class_id         INTEGER PRIMARY KEY AUTOINCREMENT,
  code_class_name       TEXT,
  code_class_start_line INTEGER,
  code_class_end_line   INTEGER
);

CREATE INDEX IF NOT EXISTS code_file_id ON code_class (code_file_id);

CREATE TABLE IF NOT EXISTS code_method(
  code_class_id          INTEGER,
  code_method_id         INTEGER PRIMARY KEY AUTOINCREMENT,
  code_method_name       TEXT,
  code_method_start_line INTEGER,
  code_method_end_line   INTEGER
);

CREATE INDEX IF NOT EXISTS code_class_id ON code_method (code_class_id);

CREATE TABLE IF NOT EXISTS code_line(
  code_file_id      INTEGER,
  code_line_id      INTEGER PRIMARY KEY AUTOINCREMENT,
  code_line_number  INTEGER,
  code_line         TEXT,
  code_line_covered INTEGER
);

CREATE INDEX IF NOT EXISTS code_line_code_file_id ON code_line (code_file_id);

CREATE TABLE IF NOT EXISTS code_coverage(
  test_id      INTEGER,
  code_line_id INTEGER
);

CREATE UNIQUE INDEX IF NOT EXISTS code_coverage_test_id_code_line_id ON code_coverage (test_id, code_line_id);
