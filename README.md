PEAR krisp::
====

krisp package 는 mod_krisp extension 을 사용하기 힘든 환경이나 또는 mod_krisp 대
신 사용할 수 있는 pure php pear code 이다.  사용법은 소스안의 test.php 를 참고하
여, 이 파일에 함수 proto type 에 대한 설명이 주석으로 제공이 된다.

이 버전은 libkrisp 3.0.0 이상의 database scheme이 필요하며, 이전 버전의 database
를 사용하려고 한다면 pear_krisp 1.x 버전을 사용하도록 한다.


## Requirements:

* libkrisp 3.0.0 이상 버전의 database shcme
* ***IPCALC*** pear package (http://pear.oops.org/)  
  ***IPCALC*** pear package는 ***pear.oops.org***에서 제공 한다. http://pear.oops.org/ 를 참
  조하여 설치를 하도록 한다. 간단하게 다음 명령으로 설치가 가능하다.

```bash
  [root@host ~]$ pear channel-discover pear.oops.org
  [root@host ~]$ pear install oops/IPCALC
```

## Usages:

소스 코드의 test.php를 참조 하도록 한다.
