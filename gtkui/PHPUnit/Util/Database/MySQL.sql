#
# PHPUnit
#
# Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
#
#   * Redistributions of source code must retain the above copyright
#     notice, this list of conditions and the following disclaimer.
# 
#   * Redistributions in binary form must reproduce the above copyright
#     notice, this list of conditions and the following disclaimer in
#     the documentation and/or other materials provided with the
#     distribution.
#
#   * Neither the name of Sebastian Bergmann nor the names of his
#     contributors may be used to endorse or promote products derived
#     from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
# "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
# LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
# FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
# COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
# LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
# LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
# ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.
#
# $Id$
#

CREATE TABLE IF NOT EXISTS run(
  run_id      INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  timestamp   INTEGER UNSIGNED NOT NULL,
  revision    INTEGER UNSIGNED NOT NULL,
  information TEXT             NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS test(
  run_id              INTEGER UNSIGNED NOT NULL REFERENCES run.run_id,
  test_id             INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  test_name           CHAR(128)        NOT NULL,
  test_result         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  test_message        TEXT             NOT NULL DEFAULT "",
  test_execution_time FLOAT   UNSIGNED NOT NULL DEFAULT 0,
  code_method_id      INTEGER UNSIGNED          REFERENCES code_method.code_method_id,
  node_root           INTEGER UNSIGNED NOT NULL,
  node_left           INTEGER UNSIGNED NOT NULL,
  node_right          INTEGER UNSIGNED NOT NULL,

  INDEX (run_id),
  INDEX (test_result),
  INDEX (code_method_id),
  INDEX (node_root),
  INDEX (node_left),
  INDEX (node_right)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_file(
  code_file_id   INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_file_name CHAR(255),
  code_file_md5  CHAR(32),
  revision       INTEGER UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_class(
  code_file_id          INTEGER UNSIGNED NOT NULL REFERENCES code_file.code_file_id,
  code_class_id         INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_class_name       CHAR(255),
  code_class_start_line INTEGER UNSIGNED NOT NULL,
  code_class_end_line   INTEGER UNSIGNED NOT NULL,

  INDEX (code_file_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_method(
  code_class_id          INTEGER UNSIGNED NOT NULL REFERENCES code_class.code_class_id,
  code_method_id         INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_method_name       CHAR(255),
  code_method_start_line INTEGER UNSIGNED NOT NULL,
  code_method_end_line   INTEGER UNSIGNED NOT NULL,

  INDEX (code_class_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_line(
  code_file_id      INTEGER UNSIGNED NOT NULL REFERENCES code_file.code_file_id,
  code_line_id      INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_line_number  INTEGER UNSIGNED NOT NULL,
  code_line         TEXT,
  code_line_covered TINYINT UNSIGNED NOT NULL,

  INDEX (code_file_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_coverage(
  test_id      INTEGER UNSIGNED NOT NULL REFERENCES test.test_id,
  code_line_id INTEGER UNSIGNED NOT NULL REFERENCES code_line.code_line_id,

  PRIMARY KEY (test_id, code_line_id)
) ENGINE=InnoDB;
