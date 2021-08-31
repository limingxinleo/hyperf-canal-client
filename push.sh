#!/usr/bin/env sh

docker login ghcr.io -u limingxinleo -p $GITHUB_TOKEN

docker tag biz-skeleton:latest ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker push ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker tag biz-skeleton:latest ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
docker push ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
