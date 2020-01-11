import { Injectable } from '@angular/core';
import { BehaviorSubject } from "rxjs";
import { DownloaderStatus } from "../../enum/downloader-status";

@Injectable({
	providedIn: 'root'
})

export class DownloaderDataService {

	private statusSource = new BehaviorSubject(<string>DownloaderStatus.Idle);

	public status = this.statusSource.asObservable();

	public startDownloadProcess(): void {
		this.statusSource.next(DownloaderStatus.Downloading);
	}

	public stopDownloadProcess(): void {
		this.statusSource.next(DownloaderStatus.Idle);
	}

	public getDownloadStatus(): void {
		this.statusSource.getValue();
	}

}
