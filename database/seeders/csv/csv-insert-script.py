#!/usr/bin/env python3
import csv
import sys

def insert_data_at_third_position(input_file, output_file, new_data):
    """
    CSVファイルの3番目の位置に新しいデータを追加する関数
    
    Args:
        input_file (str): 入力CSVファイルのパス
        output_file (str): 出力CSVファイルのパス
        new_data (str): 3番目の位置に追加するデータ
    """
    try:
        with open(input_file, 'r', newline='', encoding='utf-8') as csvfile:
            reader = csv.reader(csvfile)
            rows = list(reader)
            
            # 各行の3番目の位置にデータを追加
            for row in rows:
                if len(row) >= 2:  # 少なくとも2つの要素がある場合
                    row.insert(2, new_data)  # 0から数えて2番目の位置（3番目）に挿入
                else:
                    # 行に十分な要素がない場合、必要に応じて空の要素を追加
                    while len(row) < 2:
                        row.append('')
                    row.append(new_data)
            
            # 結果を新しいCSVファイルに書き込む
            with open(output_file, 'w', newline='', encoding='utf-8') as outfile:
                writer = csv.writer(outfile)
                writer.writerows(rows)
                
            print(f"データが正常に追加されました。出力ファイル: {output_file}")
            
    except FileNotFoundError:
        print(f"エラー: ファイル '{input_file}' が見つかりません。")
    except Exception as e:
        print(f"エラーが発生しました: {e}")

def main():
    if len(sys.argv) != 4:
        print("使用方法: python csv_insert.py <入力CSVファイル> <出力CSVファイル> <追加するデータ>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    new_data = sys.argv[3]
    
    insert_data_at_third_position(input_file, output_file, new_data)

if __name__ == "__main__":
    main()
