#!/usr/bin/env sh

echo $GITHUB_TOKEN | docker login ghcr.io -u limingxinleo --password-stdin

docker tag biz-skeleton:latest ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker push ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:${GITHUB_REF#refs/*/}
docker tag biz-skeleton:latest ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
docker push ghcr.io/limingxinleo/hyperf-canal-client/hyperf-canal-client:latest
