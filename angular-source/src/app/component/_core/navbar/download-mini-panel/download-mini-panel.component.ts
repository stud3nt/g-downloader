import { Component, OnInit } from '@angular/core';
import { ParsedFile } from "../../../../model/parsed-file";
import { DownloaderStatus } from "../../../../enum/downloader-status";
import { DownloaderService } from "../../../../service/downloader.service";
import { DownloaderDataService } from "../../../../service/data/downloader-data.service";
import { JsonResponse } from "../../../../model/json-response";
import { RouterService } from "../../../../service/router.service";
import { FileType } from "../../../../enum/file-type";

@Component({
	selector: 'app-download-mini-panel',

	templateUrl: './download-mini-panel.component.html'
})
export class DownloadMiniPanelComponent implements OnInit {

	public mainButtonClass: string = 'navbar-main-button inactive';

	public downloaderStatus: string = DownloaderStatus.Idle;

	public queuedFilesCount: number = 0;
	public queuedFilesSize: string = '0 bytes';

	public queuedFiles: ParsedFile[] = [];

	public DownloaderStatus = DownloaderStatus;
	public FileType = FileType;

	constructor(
		protected downloaderService: DownloaderService,
		protected downloaderDataService: DownloaderDataService,
		public routerService: RouterService
	) { }

	ngOnInit() {
		this.checkStatus();
		this.downloaderDataService.status.subscribe((status) => {
			if (status === this.downloaderStatus)
				return;

			switch (status) {
				case DownloaderStatus.Idle:
					this.start();
					break;

				case DownloaderStatus.Downloading:
					this.stop();
					break;
			}
		})
	}

	public start(): void {
		this.downloaderService.setDownloaderStatus(
			DownloaderStatus.Downloading
		).subscribe((response: JsonResponse) => {
			if (response.success()) {
				this.downloaderStatus = DownloaderStatus.Downloading;
			}
		});
	}

	public stop(): void {
		this.downloaderService.setDownloaderStatus(
			DownloaderStatus.Idle
		).subscribe((response: JsonResponse) => {
			if (response.success()) {
				this.downloaderStatus = DownloaderStatus.Idle;
			}
		});
	}

	public checkStatus(): void {
		this.downloaderService.checkDownloaderStatus().subscribe((response: JsonResponse) => {
			this.queuedFilesCount = parseInt(response.data['queuedFilesCount']);
			this.queuedFilesSize = response.data['queuedFilesSize'];
			this.downloaderStatus = response.data['downloaderStatus'];

			this.queuedFiles = [];

			if (response.data['queuedFiles']) {
				for (let queuedFile of response.data['queuedFiles']) {
					this.queuedFiles.push(new ParsedFile(queuedFile));
				}
			}
		});
	}

	protected determineClasses(): void {
		switch (this.downloaderStatus) {
			case DownloaderStatus.Idle:
				this.mainButtonClass = 'navbar-main-button inactive';
				break;

			case DownloaderStatus.Downloading:
				this.mainButtonClass = 'navbar-main-button active';
				break;

			case DownloaderStatus.WaitingForResponse:
				this.mainButtonClass = 'navbar-main-button waiting';
				break;
		}
	}

}
