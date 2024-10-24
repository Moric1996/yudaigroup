# ベースイメージとしてUbuntu 22.04を指定
FROM ubuntu:22.04

# PHPバージョンの指定
ENV PHP_VERSION 5.6
ENV DEBIAN_FRONTEND=noninteractive

# パッケージのアップデートと必要なリポジトリの追加
RUN apt-get update && apt-get install -y \
    software-properties-common \
    wget \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update

# PostgreSQLの公式リポジトリを追加し、9.6クライアントをインストール
RUN sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list' \
    && wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
    && apt-get update \
    && apt-get install -y postgresql-client-9.6

# PHP 5.6と関連パッケージのインストール
RUN apt-get install -y \
    php${PHP_VERSION} \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-pdo \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-mcrypt \
    php${PHP_VERSION}-pgsql \
    php-pear \
    php-xdebug \
    zip \
    unzip \
    apache2 \
    gcc

# プロジェクトフォルダをドキュメントルートにコピー
COPY . /var/www/html/yudaigroup

# Apacheのmod_rewriteを有効化
RUN a2enmod rewrite

# 不要なファイルのクリーンアップ
RUN apt-get clean

# Apacheの起動コマンド
CMD ["/usr/sbin/apachectl", "-DFOREGROUND"]
