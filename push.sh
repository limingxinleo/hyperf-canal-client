#!/usr/bin/env sh

docker login docker.pkg.github.com -u limingxinleo -p $GITHUB_TOKEN

docker tag biz-skeleton:latest docker.pkg.github.com/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker push docker.pkg.github.com/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker tag biz-skeleton:latest docker.pkg.github.com/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
docker push docker.pkg.github.com/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
