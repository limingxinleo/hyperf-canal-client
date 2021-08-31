#!/usr/bin/env sh

docker login docker.pkg.github.com -u limingxinleo -p $GITHUB_TOKEN

docker tag biz-skeleton:latest docker.pkg.github.com/limingxinleo/hyperf-canal-client/canal-client:$CLIENT_VERSION
docker push docker.pkg.github.com/limingxinleo/hyperf-canal-client/canal-client:$CLIENT_VERSION
docker tag biz-skeleton:latest docker.pkg.github.com/limingxinleo/hyperf-canal-client/canal-client:latest
docker push docker.pkg.github.com/limingxinleo/hyperf-canal-client/canal-client:latest
