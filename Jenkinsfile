@Library('jenkins-pipeline')_

pipeline {
    agent any
    stages {
        stage('Deployment staging') {
            when {
                branch 'release'
            }
            steps {
                // Update git repos.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod composer install --no-dev -o'"

                // Run migrations.
                sh "ansible srvitkphp72stg -m shell -a 'cd /data/www/economics_srvitkphp72stg_itkdev_dk/htdocs; APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction'"

                // Build assets
                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest install'
                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest build'

                // Copy encore assets.
                sh "ansible srvitkphp72stg -m synchronize -a 'src=${WORKSPACE}/public/build/ dest=/data/www/economics_srvitkphp72stg_itkdev_dk/htdocs/public/build'"
            }
        }
        stage('Deployment production') {
            when {
                branch 'master'
            }
            steps {
                timeout(time: 30, unit: 'MINUTES') {
                    input 'Should the site be deployed?'
                }
                // Update git repos.
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git clean -d --force'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git checkout ${BRANCH_NAME}'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git fetch'"
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; git reset origin/${BRANCH_NAME} --hard'"

                // Run composer.
                sh "ansible srvitkeconomics -m shell -a 'cd /data/www/portal_itkdev_dk/htdocs; composer install --no-dev -o'"

                // Build assets
                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest install'
                sh 'docker run -v $WORKSPACE:/app -v /var/lib/jenkins/.yarn-cache:/usr/local/share/.cache/yarn:rw itkdev/yarn:latest build'

                // Copy encore assets.
                sh "ansible srvitkeconomics -m synchronize -a 'src=${WORKSPACE}/public/build/ dest=/data/www/portal_itkdev_dk/htdocs/public/build'"
            }
        }
    }
}
