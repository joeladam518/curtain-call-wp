FROM wordpress

WORKDIR /var/www/html/wp-content/plugins/CurtainCallWP

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC
ENV LL_ALIAS="alias ll='ls --color=auto --group-directories-first -alphv'"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install packages under Debian
RUN apt-get update \
        && apt-get install -y \
            apt-transport-https \
            ca-certificates \
            git \
            unzip \
            vim \
            wget \
            zip

# ll alias
RUN grep -qF "$LL_ALIAS" ~/.bashrc || echo "$LL_ALIAS" >> ~/.bashrc

# Install composer
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

# Install the latest stable Xdebug via PECL (builds against the image's PHP)
# This keeps Xdebug up-to-date without pinning a specific branch.
RUN pecl install xdebug \
    && rm -rf /tmp/pear

# Since this Dockerfile extends the official Docker image `wordpress`,
# and since `wordpress`, in turn, extends the official Docker image `php`,
# the helper script docker-php-ext-enable (defined for image `php`)
# works here, and we can use it to enable xdebug:
RUN docker-php-ext-enable xdebug
