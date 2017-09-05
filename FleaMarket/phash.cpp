#include <iostream>
#include <opencv2/opencv.hpp>
#include <mysql++.h>
using namespace cv;
using namespace std;
using namespace mysqlpp;

// pHash算法  
int* pHashValue(Mat &matSrc)
{
	//压缩图片
	resize(matSrc, matSrc, Size(357, 419), 0, 0, INTER_NEAREST);
	Mat matDst;
	resize(matSrc, matDst, Size(8, 8), 0, 0, INTER_CUBIC);

	//检查图片通道数
	if(matDst.channels() == 3){
		//将图片转为灰度图片
		cvtColor(matDst, matDst, CV_BGR2GRAY);
	}

	int iAvg = 0;
	int* arr = new int[64];

	//求取DCT系数均值（左上角8*8区块的DCT系数）
	for (int i = 0; i < 8; i++) {  
		uchar* data = matDst.ptr<uchar>(i);  
		int tmp = i * 8;  
		for (int j = 0; j < 8; j++) {  
			int tmp1 = tmp + j;  
			arr[tmp1] = data[j] / 4 * 4;  
			iAvg += arr[tmp1];  
		}  
	}  

	iAvg /= 64;

	//计算哈希值
	for (int i = 0; i < 64; i++) {
		arr[i] = (arr[i] >= iAvg) ? 1 : 0;
	}
	return arr;
}

//汉明距离计算  
int HanmingDistance(string arr1[],string arr2[])  
{  
	int iDiffNum = 0;

	for (int i = 0; i < 64; i++)  
		if (arr1[i] != arr2[i])  
			++iDiffNum;

	cout<<"iDiffNum = "<<iDiffNum<<endl;  

	if (iDiffNum <= 5)  
		cout<<"一样的"<<endl;  
	else if (iDiffNum > 10)  
		cout<<"不一样的"<<endl;  
	else  
		cout<<"蛮像的"<<endl;
}



int main(int argc, char ** argv){

	//连接数据库
	const char* db = "xunwu", *server = "127.0.0.1", *user = "root", *password = "113233";
	mysqlpp::Connection conn(false);
	//设置字符集
	conn.set_option(new mysqlpp::SetCharsetNameOption("utf8"));
	if(conn.connect(db,server,user,password)){
		//查询数据
		mysqlpp::Query query = conn.query("select gID,gImage,imghash from goods");
		query << " where gID = " << mysqlpp::quote << argv[1];
		mysqlpp::StoreQueryResult res = query.store();
		if(res[0]["gImage"][0] == 'p'){

			//得到图片的路径
			Mat matSrc;
			string imgpath(26,'\0');
			for(int i = 0; i < 26 && res[0]["gImage"][i]; i++){
				imgpath[i] = res[0]["gImage"][i];
			}
			cout<<"Imagepaht is :"<<imgpath<<endl;

			//计算图片的哈希值
			matSrc = imread(imgpath, CV_LOAD_IMAGE_COLOR);
			int* arr = new int[64];
			arr = pHashValue(matSrc);
			string str(64,'\0');
			for(int i = 0; i < 64; i++)
				str[i] = arr[i] + '0';

			cout<<"Phash is : "<<str<<endl;

			//更新数据
			Query query = conn.query();
			query << "update goods set imghash = '" << str << "' where gID = " << res[0]["gID"];

			cout<<query<<endl;

			SimpleResult r = query.execute();
			cout<<"Inserted success"<<endl;
			return 0;
		}
		else{
			cout<<"Failed to get data："<<query.error()<<endl;
			return 1;
		}
	}
	else{
		cout<<"DB connection failed: "<<conn.error()<<endl;
		return 1;
	}
}